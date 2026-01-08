<?php

namespace App\Helpers\ConvertImage;

use Exception;

class ConvertImage
{
    public static function convertImageToWebP($imagePath, $outputPath, $quality = 80)
    {
        // Pastikan direktori output ada
        $outputDir = dirname($outputPath);
        if (! file_exists($outputDir)) {
            if (! mkdir($outputDir, 0755, true) && ! is_dir($outputDir)) {
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

        if (! $image) {
            throw new Exception("Gagal memuat gambar: $imagePath");
        }

        // Konversi palette image ke true color untuk mendukung WebP
        if (! imageistruecolor($image)) {
            $width = imagesx($image);
            $height = imagesy($image);

            // Buat image true color baru
            $trueColorImage = imagecreatetruecolor($width, $height);

            // Pertahankan transparansi jika ada
            imagealphablending($trueColorImage, false);
            imagesavealpha($trueColorImage, true);

            // Copy image palette ke true color
            imagecopy($trueColorImage, $image, 0, 0, 0, 0, $width, $height);

            // Hapus image lama dan gunakan yang baru
            imagedestroy($image);
            $image = $trueColorImage;
        }

        // Convert dan simpan sebagai WebP
        if (! imagewebp($image, $outputPath, $quality)) {
            imagedestroy($image);
            throw new Exception('Gagal mengonversi gambar ke format WebP');
        }

        // Bebaskan memori
        imagedestroy($image);

        return $outputPath;
    }
}
