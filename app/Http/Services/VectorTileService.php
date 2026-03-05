<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class VectorTileService
{
    /**
     * Threshold ukuran file di mana konversi vector tile otomatis dilakukan.
     * File di bawah threshold tetap di-render sebagai GeoJSON biasa.
     */
    const SIZE_THRESHOLD = 20 * 1024 * 1024; // 20MB

    /**
     * Cek apakah file GeoJSON perlu diconvert ke vector tile berdasarkan ukuran.
     */
    public function shouldConvert(string $storagePath): bool
    {
        $fullPath = Storage::disk('public')->path($storagePath);

        return file_exists($fullPath) && filesize($fullPath) > self::SIZE_THRESHOLD;
    }

    /**
     * Convert GeoJSON ke MBTiles menggunakan Tippecanoe (via Docker).
     *
     * FLAGS LOSSLESS:
     * -zg                              : Auto-detect max zoom level
     * --no-feature-limit               : Jangan batasi jumlah fitur per tile
     * --no-tile-size-limit             : Jangan batasi ukuran tile
     * --no-simplification-of-shared-nodes : Jangan simplify shared vertices
     * --no-tiny-polygon-reduction      : Jangan hapus polygon kecil
     * -ai                              : Include semua atribut/properties
     * --force                          : Overwrite output file
     *
     * @param  string  $geojsonStoragePath  Path relatif ke storage/app/public
     * @param  string  $layerName  Nama layer (untuk identifikasi)
     * @return string|null Path relatif ke .mbtiles jika sukses, null jika gagal
     */
    public function convertToMBTiles(string $geojsonStoragePath, string $layerName): ?string
    {
        $geojsonFullPath = Storage::disk('public')->path($geojsonStoragePath);

        if (!file_exists($geojsonFullPath)) {
            Log::error("VectorTile: GeoJSON file not found: {$geojsonFullPath}");

            return null;
        }

        // Output directory: storage/app/public/tiles/
        $tilesDir = 'tiles';
        Storage::disk('public')->makeDirectory($tilesDir);

        // Nama file yang aman (slug + random untuk hindari collision)
        $safeName = Str::slug($layerName) . '-' . Str::random(8);
        $mbtilesRelPath = "{$tilesDir}/{$safeName}.mbtiles";
        $mbtilesFullPath = Storage::disk('public')->path($mbtilesRelPath);

        // Hapus file lama jika ada
        if (file_exists($mbtilesFullPath)) {
            unlink($mbtilesFullPath);
        }

        // Tentukan engine eksekusi (bisa diatur di .env via TIPPECANOE_ENGINE)
        // Default ke 'wsl' jika berjalan di Windows, sebaliknya 'docker'
        $defaultEngine = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'wsl' : 'docker';
        $engine = env('TIPPECANOE_ENGINE', $defaultEngine);

        // Build command berdasarkan engine
        // Sanitasi nama layer untuk mencegah command injection
        $safeLayerName = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $layerName);

        if ($engine === 'wsl') {
            // Laragon / Windows Localhost -> Eksekusi via WSL Ubuntu
            // Kita perlu mengubah path C:\...\ rtrw-be\... menjadi /mnt/c/...
            $wslInputPath = $this->convertToWslPath($geojsonFullPath);
            $wslOutputPath = $this->convertToWslPath($mbtilesFullPath);

            $command = [
                'wsl',
                'tippecanoe',
                '-o',
                $wslOutputPath,
                '-zg',
                '--no-feature-limit',
                '--no-tile-size-limit',
                '--no-simplification-of-shared-nodes',
                '--no-tiny-polygon-reduction',
                '-ai',
                '--force',
                '-l',
                $safeLayerName,
                $wslInputPath,
            ];
        } elseif ($engine === 'native') {
            // Native Linux (Server Production / VPS)
            $command = [
                'tippecanoe',
                '-o',
                $mbtilesFullPath,
                '-zg',
                '--no-feature-limit',
                '--no-tile-size-limit',
                '--no-simplification-of-shared-nodes',
                '--no-tiny-polygon-reduction',
                '-ai',
                '--force',
                '-l',
                $safeLayerName,
                $geojsonFullPath,
            ];
        } else {
            // Docker (Default Fallback)
            $geojsonDir = dirname($geojsonFullPath);
            $geojsonFile = basename($geojsonFullPath);
            $mbtilesDir = dirname($mbtilesFullPath);
            $mbtilesFile = basename($mbtilesFullPath);

            $command = [
                'docker',
                'run',
                '--rm',
                '-v',
                "{$geojsonDir}:/input:ro",
                '-v',
                "{$mbtilesDir}:/output",
                'felt/tippecanoe:latest',
                'tippecanoe',
                '-o',
                "/output/{$mbtilesFile}",
                '-zg',
                '--no-feature-limit',
                '--no-tile-size-limit',
                '--no-simplification-of-shared-nodes',
                '--no-tiny-polygon-reduction',
                '-ai',
                '--force',
                '-l',
                $safeLayerName,
                "/input/{$geojsonFile}",
            ];
        }

        Log::info('VectorTile: Starting conversion', [
            'engine' => $engine,
            'layer' => $layerName,
            'input' => $geojsonFullPath,
            'input_size_mb' => round(filesize($geojsonFullPath) / 1024 / 1024, 2),
            'output' => $mbtilesFullPath,
        ]);

        try {
            $process = new Process($command);
            $process->setTimeout(3600); // 1 jam timeout untuk file besar (1GB+)
            $process->run();

            if (!$process->isSuccessful()) {
                Log::error('VectorTile: Conversion failed', [
                    'layer' => $layerName,
                    'exit_code' => $process->getExitCode(),
                    'stderr' => substr($process->getErrorOutput(), 0, 2000),
                    'stdout' => substr($process->getOutput(), 0, 2000),
                ]);

                return null;
            }

            $outputSize = file_exists($mbtilesFullPath) ? filesize($mbtilesFullPath) : 0;

            Log::info('VectorTile: Conversion successful', [
                'layer' => $layerName,
                'output_size_mb' => round($outputSize / 1024 / 1024, 2),
            ]);

            return $mbtilesRelPath;

        } catch (\Exception $e) {
            Log::error('VectorTile: Exception during conversion', [
                'layer' => $layerName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Proses otomatis: cek ukuran dan convert jika perlu.
     * Mengembalikan array dengan tile_path dan render_type.
     *
     * @param  string  $geojsonStoragePath  Path relatif ke file GeoJSON
     * @param  string  $layerName  Nama layer
     * @return array ['tile_path' => string|null, 'render_type' => 'geojson'|'vectortile']
     */
    public function processIfNeeded(string $geojsonStoragePath, string $layerName): array
    {
        if (!$this->shouldConvert($geojsonStoragePath)) {
            return [
                'tile_path' => null,
                'render_type' => 'geojson',
            ];
        }

        $tilePath = $this->convertToMBTiles($geojsonStoragePath, $layerName);

        if ($tilePath) {
            return [
                'tile_path' => $tilePath,
                'render_type' => 'vectortile',
            ];
        }

        // Konversi gagal — fallback ke GeoJSON
        Log::warning("VectorTile: Falling back to GeoJSON for {$layerName}");

        return [
            'tile_path' => null,
            'render_type' => 'geojson',
        ];
    }

    /**
     * Konversi path Windows (C:\...) menjadi path WSL (/mnt/c/...)
     */
    private function convertToWslPath(string $windowsPath): string
    {
        $wslPath = str_replace('\\', '/', $windowsPath);
        if (preg_match('/^([A-Za-z]):\/(.*)/', $wslPath, $matches)) {
            $drive = strtolower($matches[1]);
            $wslPath = "/mnt/{$drive}/{$matches[2]}";
        }
        return $wslPath;
    }
}
