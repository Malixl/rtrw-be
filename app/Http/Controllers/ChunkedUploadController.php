<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * ChunkedUploadController
 * 
 * Menangani resumable chunked file upload mirip perilaku YouTube.
 * Setiap chunk = 1 HTTP request kecil (5MB), sehingga:
 * - Nginx hanya perlu client_max_body_size 10M (bukan 500M)
 * - PHP memory_limit tetap aman di 128M
 * - Jika gagal, bisa dilanjutkan dari chunk terakhir (resume)
 */
class ChunkedUploadController extends Controller
{
    use ApiResponse;

    /**
     * Direktori temporary untuk menyimpan chunk.
     * Menggunakan disk 'local' (storage/app/) agar tidak ter-expose ke publik.
     */
    private const TMP_DIR = 'tmp';

    /**
     * Endpoint 1: Upload satu chunk file.
     * 
     * POST /upload-chunk
     * Body: file_id (UUID), chunk_index (int), chunk_file (binary)
     * 
     * Setiap chunk disimpan sebagai file terpisah di:
     * storage/app/tmp/{file_id}/chunk_{index}
     */
    public function uploadChunk(Request $request)
    {
        $request->validate([
            'file_id'     => 'required|uuid',
            'chunk_index' => 'required|integer|min:0',
            'chunk_file'  => 'required|file',
        ]);

        $fileId     = $request->input('file_id');
        $chunkIndex = (int) $request->input('chunk_index');
        $chunkFile  = $request->file('chunk_file');

        try {
            // Buat folder tmp/{file_id}/ jika belum ada
            $chunkDir = self::TMP_DIR . '/' . $fileId;

            // Simpan chunk dengan nama deterministik: chunk_0, chunk_1, ...
            $chunkFileName = 'chunk_' . $chunkIndex;
            $chunkFile->storeAs($chunkDir, $chunkFileName, 'local');

            return $this->successResponseWithData(
                ['chunk_index' => $chunkIndex],
                "Chunk {$chunkIndex} berhasil diupload",
                Response::HTTP_OK
            );
        } catch (\Throwable $e) {
            Log::error("Gagal upload chunk {$chunkIndex} untuk file_id {$fileId}: " . $e->getMessage());
            return $this->errorResponse(
                'Gagal menyimpan chunk. Silakan coba lagi.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Endpoint 2: Cek status upload — chunk mana saja yang sudah tersimpan.
     * 
     * GET /upload-status?file_id=xxx
     * 
     * Frontend WAJIB memanggil ini sebelum mulai upload untuk:
     * 1. Mengetahui chunk mana yang sudah berhasil (skip)
     * 2. Melanjutkan upload dari posisi terakhir (resume)
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'file_id' => 'required|uuid',
        ]);

        $fileId  = $request->input('file_id');
        $chunkDir = self::TMP_DIR . '/' . $fileId;

        $uploadedChunks = [];

        // Cek apakah folder tmp/{file_id} ada
        if (Storage::disk('local')->exists($chunkDir)) {
            // Scan semua file di folder, extract index dari nama file
            $files = Storage::disk('local')->files($chunkDir);

            foreach ($files as $file) {
                $basename = pathinfo($file, PATHINFO_BASENAME);
                // Format nama: chunk_0, chunk_1, chunk_2, ...
                if (preg_match('/^chunk_(\d+)$/', $basename, $matches)) {
                    $uploadedChunks[] = (int) $matches[1];
                }
            }

            // Sort ascending agar frontend mudah memproses
            sort($uploadedChunks);
        }

        return $this->successResponseWithData(
            ['uploaded_chunks' => $uploadedChunks],
            'Status upload berhasil diambil',
            Response::HTTP_OK
        );
    }

    /**
     * Endpoint 3: Merge semua chunk menjadi satu file final.
     * 
     * POST /merge-chunks
     * Body: file_id (UUID), total_chunks (int), original_name (string)
     * 
     * ===============================================================
     * CRITICAL: STREAM-BASED MERGE — JANGAN GUNAKAN file_get_contents!
     * ===============================================================
     * 
     * Proses merge menggunakan fopen mode 'wb' + stream_copy_to_stream().
     * Setiap chunk dibaca dan ditulis secara streaming, sehingga RAM usage
     * tetap konstan (~2-4MB) meskipun file final mencapai 500MB+.
     * 
     * Urutan operasi:
     * 1. Validasi semua chunk ada (0 sampai total_chunks - 1)
     * 2. Buat file output kosong dengan fopen('wb')
     * 3. Loop setiap chunk: fopen('rb') → stream_copy_to_stream() → fclose()
     * 4. Pindahkan file final ke storage/app/public/
     * 5. Hapus folder temporary
     */
    public function mergeChunks(Request $request)
    {
        $request->validate([
            'file_id'       => 'required|uuid',
            'total_chunks'  => 'required|integer|min:1',
            'original_name' => 'required|string',
        ]);

        $fileId      = $request->input('file_id');
        $totalChunks = (int) $request->input('total_chunks');
        $originalName = $request->input('original_name');

        $chunkDir = self::TMP_DIR . '/' . $fileId;

        try {
            // ========================
            // STEP 1: Validasi semua chunk ada
            // ========================
            $missingChunks = [];
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $chunkDir . '/chunk_' . $i;
                if (!Storage::disk('local')->exists($chunkPath)) {
                    $missingChunks[] = $i;
                }
            }

            if (!empty($missingChunks)) {
                return $this->errorResponseWithData(
                    ['missing_chunks' => $missingChunks],
                    'Beberapa chunk belum diupload. Silakan upload ulang chunk yang hilang.',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // ========================
            // STEP 2: Siapkan path file final
            // ========================
            // Tentukan ekstensi dari nama asli (biasanya .geojson)
            $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'geojson';
            $finalFileName = Str::random(20) . '-' . time() . '.' . $extension;
            $publicFolder  = 'data_spasial_service';
            $finalRelativePath = $publicFolder . '/' . $finalFileName;

            // Path absolut ke file output di disk 'public'
            $finalAbsolutePath = Storage::disk('public')->path($finalRelativePath);

            // Pastikan folder tujuan ada
            $finalDir = dirname($finalAbsolutePath);
            if (!is_dir($finalDir)) {
                mkdir($finalDir, 0775, true);
            }

            // Naikkan time limit untuk merge file besar (500MB+)
            // Ini hanya berlaku untuk request ini, bukan global
            set_time_limit(300);

            // ========================
            // STEP 3: STREAM MERGE — inti dari efisiensi memory
            // ========================
            // Buka file output SEKALI, lalu append setiap chunk secara streaming.
            // RAM usage: ~buffer size PHP internal (8KB default), bukan ukuran file.
            $outputHandle = fopen($finalAbsolutePath, 'wb');
            if (!$outputHandle) {
                throw new \Exception("Gagal membuat file output: {$finalAbsolutePath}");
            }

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkAbsPath = Storage::disk('local')->path($chunkDir . '/chunk_' . $i);

                // Buka chunk untuk dibaca secara streaming
                $chunkHandle = fopen($chunkAbsPath, 'rb');
                if (!$chunkHandle) {
                    fclose($outputHandle);
                    // Cleanup file output yang belum selesai
                    @unlink($finalAbsolutePath);
                    throw new \Exception("Gagal membaca chunk_{$i}");
                }

                // stream_copy_to_stream: copy dari satu stream ke stream lain
                // TANPA memuat isi file ke memory (zero-copy di sisi PHP)
                $bytesCopied = stream_copy_to_stream($chunkHandle, $outputHandle);
                fclose($chunkHandle);

                if ($bytesCopied === false) {
                    fclose($outputHandle);
                    @unlink($finalAbsolutePath);
                    throw new \Exception("Gagal menulis chunk_{$i} ke file output");
                }
            }

            fclose($outputHandle);

            // ========================
            // STEP 4: Validasi ringan — cek file looks like JSON
            // ========================
            $checkHandle = fopen($finalAbsolutePath, 'r');
            if ($checkHandle) {
                $firstBytes = fread($checkHandle, 1024);
                fclose($checkHandle);
                $trimmed = trim($firstBytes);
                if (!empty($trimmed) && $trimmed[0] !== '{' && $trimmed[0] !== '[') {
                    @unlink($finalAbsolutePath);
                    throw new \Exception("File hasil merge bukan format JSON/GeoJSON yang valid.");
                }
            }

            // ========================
            // STEP 5: Bersihkan folder temporary
            // ========================
            Storage::disk('local')->deleteDirectory($chunkDir);

            Log::info("Chunked upload merged: file_id={$fileId}, chunks={$totalChunks}, output={$finalRelativePath}");

            return $this->successResponseWithData(
                ['merged_path' => $finalRelativePath],
                'File berhasil digabungkan',
                Response::HTTP_OK
            );
        } catch (\Throwable $e) {
            Log::error("Gagal merge chunks untuk file_id {$fileId}: " . $e->getMessage());
            return $this->errorResponse(
                'Gagal menggabungkan file: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
