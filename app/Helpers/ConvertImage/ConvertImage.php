<?php

namespace App\Helpers\ConvertImage;

use Exception;

class ConvertImage
{
    public static function convertImageToWebP($imagePath, $outputPath, $quality = 80)
    {
        // Pastikan direktori output ada
        $outputDir = dirname($outputPath);
        if (!file_exists($outputDir)) {
            if (!mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
                throw new Exception("Gagal membuat direktori: $outputDir");
            }
        }

        // Get image extension
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

        // Load the image berdasarkan ekstensi
        switch (strtolower($extension)) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($imagePath);
                break;
            default:
                throw new Exception("Format gambar tidak didukung: $extension");
        }

        if (!$image) {
            throw new Exception("Gagal memuat gambar: $imagePath");
        }

        // Convert dan simpan sebagai WebP
        if (!imagewebp($image, $outputPath, $quality)) {
            imagedestroy($image);
            throw new Exception("Gagal mengonversi gambar ke format WebP");
        }

        // Bebaskan memori
        imagedestroy($image);

        return $outputPath;
    }
}
