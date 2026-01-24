<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StorageController extends Controller
{
    /**
     * Proxy untuk melayani file storage dengan CORS dan Middleware
     * Solusi untuk Ngrok/Vercel yang memblokir static files
     */
    public function show($filename)
    {
        \Illuminate\Support\Facades\Log::info("StorageProxy Request: " . $filename);
        
        // Decode filename jika ada special characters
        $filename = urldecode($filename);
        
        // Pastikan path relatif terhadap storage/app/public
        // Mencegah directory traversal sederhana
        if (strpos($filename, '..') !== false) {
             return response()->json(['error' => 'Invalid path'], 400);
        }

        if (!Storage::disk('public')->exists($filename)) {
            // FALLBACK LOGIC: Coba cari file dengan prefix yang sama
            // Berguna jika nama file di DB terpotong atau berbeda sedikit dengan di disk
            $directory = dirname($filename);
            $basename = basename($filename);
            
            // Ambil 20 karakter pertama sebagai identifier unik
            $prefix = substr($basename, 0, 20);
            
            if (strlen($prefix) > 10) {
                $files = Storage::disk('public')->files($directory);
                foreach ($files as $file) {
                    if (str_starts_with(basename($file), $prefix)) {
                        \Illuminate\Support\Facades\Log::info("StorageProxy Fallback: Requested $filename, Serving $file");
                        $filename = $file;
                        goto found;
                    }
                }
            }

            \Illuminate\Support\Facades\Log::error("StorageProxy 404: " . $filename);
            return response()->json(['error' => 'File not found'], 404);
        }

        found:
        $path = Storage::disk('public')->path($filename);
        $mimeType = Storage::disk('public')->mimeType($filename);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=31536000', // Cache 1 tahun
            'ngrok-skip-browser-warning' => 'true'
        ]);
    }
}
