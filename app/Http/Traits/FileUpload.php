<?php

namespace App\Http\Traits;

use App\Helpers\ConvertImage\ConvertImage;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Storage;

trait FileUpload
{
    /**
     * Upload dokumen.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param string $disk
     * @return string Path file yang disimpan
     */
    public function uploadDocument($file, array $allowedExtensions = ['geojson', 'png', 'pdf'], string $folder = 'documents', string $disk = 'public'): string
    {
        $extension = $file->getClientOriginalExtension();

        if (!in_array(strtolower($extension), $allowedExtensions)) {
            throw new \Exception("Ekstensi file tidak diizinkan.");
        }

        // Simpan file dengan ekstensi asli
        $fileName = Str::random(20) . '-' . time(); // Buatkan nama file yang random
        $fileNameWithExtension = $fileName . '.' . $extension; // Tambahkan ekstensi asli

        return $file->storeAs($folder, $fileNameWithExtension, $disk);
    }

    public function uploadDocumentBase64($base64String, string $path): string
    {
        try {
            // Jika string memiliki header MIME
            if (preg_match('/^data:[\w\/]+;base64,/', $base64String)) {
                list($type, $data) = explode(';', $base64String);
                list(, $data)      = explode(',', $data);
                $mimeType = str_replace('data:', '', $type);

                // Validasi hanya PDF yang diizinkan
                if ($mimeType !== 'application/pdf') {
                    throw new Exception('Hanya file PDF yang diizinkan.');
                }
            } else {
                // Jika base64 mentah, asumsikan PDF
                $data = $base64String;
            }

            // Decode data base64
            $decodedFile = base64_decode($data);
            if ($decodedFile === false) {
                throw new Exception('Gagal mendecode base64');
            }

            // Hanya gunakan ekstensi .pdf
            $fileName = uniqid() . '.pdf';
            $filePath = $path . '/' . $fileName;

            // Pastikan direktori tujuan ada
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Simpan file
            $success = file_put_contents($filePath, $decodedFile);
            if ($success === false) {
                throw new Exception('Gagal menyimpan file');
            }

            // Kembalikan hanya nama file
            return $fileName;
        } catch (Exception $e) {
            throw new Exception('Error saat mengunggah dokumen: ' . $e->getMessage());
        }
    }

    public function uploadPhotoAndConvertToWebP($file, string $folder = 'uploads', string $disk = 'public'): string
    {
        $filePath = $file->store($folder, $disk);
        $extension = $file->getClientOriginalExtension();
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            throw new \Exception("File extension not allowed.");
        }


        if (strtolower($extension) !== 'webp') {
            $webpPath = storage_path("app/{$disk}/" . $folder . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '.webp');
            ConvertImage::convertImageToWebP(storage_path("app/{$disk}/" . $filePath), $webpPath);

            Storage::disk($disk)->delete($filePath);

            $filePath = "{$folder}/" . pathinfo($webpPath, PATHINFO_BASENAME);
        }

        return $filePath;
    }

     public function unlinkPhoto(?string $filePath, string $disk = 'public'): bool
    {
        if (empty($filePath)) {
            return true;
        }

        return Storage::disk($disk)->delete($filePath);
    }



    public function unlinkFile(?string $filePath, string $disk = 'public'): bool
    {
        if (empty($filePath)) {
            return true;
        }

        return Storage::disk($disk)->delete($filePath);
    }
}
