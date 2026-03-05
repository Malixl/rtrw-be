<?php

namespace App\Console\Commands;

use App\Http\Services\VectorTileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConvertExistingGeoJsonToTiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geojson:convert-tiles {--force : Force conversion even for files under 20MB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing GeoJSON files to Vector Tiles (.mbtiles) for all spatial tables';

    /**
     * Tables to process
     */
    protected $tables = [
        'polaruang',
        'struktur_ruang',
        'ketentuan_khusus',
        'kawasan_strategi_provinsi',
        'data_spasial',
        'batas_administrasi',
    ];

    /**
     * Execute the console command.
     */
    public function handle(VectorTileService $vectorTileService)
    {
        $force = $this->option('force');
        $this->info("Starting batch conversion of existing GeoJSON to Vector Tiles...");

        $totalConverted = 0;
        $totalFailed = 0;
        $totalSkipped = 0;

        foreach ($this->tables as $table) {
            $this->warn("\nProcessing table: {$table}");

            // Get records that haven't been converted yet
            $records = DB::table($table)
                ->whereNotNull('geojson_file')
                ->where('render_type', 'geojson')
                ->get();

            $bar = $this->output->createProgressBar(count($records));

            foreach ($records as $record) {
                $storagePath = $record->geojson_file;
                $layerName = $record->nama ?? "{$table}_{$record->id}";

                $fullPath = Storage::disk('public')->path($storagePath);

                if (!file_exists($fullPath)) {
                    $this->error("\nFile not found: {$storagePath}");
                    $totalFailed++;
                    $bar->advance();
                    continue;
                }

                $sizeMB = round(filesize($fullPath) / 1024 / 1024, 2);

                // Check threshold unless forced
                if (!$force && !$vectorTileService->shouldConvert($storagePath)) {
                    $totalSkipped++;
                    $bar->advance();
                    continue;
                }

                $this->info("\nConverting: {$layerName} ({$sizeMB} MB)");

                // Perform conversion
                $tilePath = $vectorTileService->convertToMBTiles($storagePath, $layerName);

                if ($tilePath) {
                    // Update DB
                    DB::table($table)->where('id', $record->id)->update([
                        'tile_path' => $tilePath,
                        'render_type' => 'vectortile',
                        'updated_at' => now()
                    ]);
                    $totalConverted++;
                    $this->info("✓ Success");
                } else {
                    $totalFailed++;
                    $this->error("✕ Failed");
                }

                $bar->advance();
            }
            $bar->finish();
            $this->line("");
        }

        $this->info("\n=================================");
        $this->info("Batch Conversion Summary:");
        $this->info("Successfully converted : {$totalConverted}");
        $this->info("Skipped (under 20MB)   : {$totalSkipped}");
        $this->info("Failed to convert      : {$totalFailed}");
        $this->info("=================================");
    }
}
