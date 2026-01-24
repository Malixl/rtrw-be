<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk mengompresi response dengan Gzip
 * Khusus untuk response berukuran besar seperti GeoJSON
 */
class GzipResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Debug header untuk memastikan middleware berjalan
        $response->headers->set('X-Gzip-Middleware', 'Active');
        
        // Skip jika sudah terkompresi atau client tidak mendukung gzip
        if (!$this->shouldCompress($request, $response)) {
            $response->headers->set('X-Gzip-Status', 'Skipped');
            return $response;
        }
        
        // Handle BinaryFileResponse (file download)
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            $path = $response->getFile()->getPathname();
            $content = file_get_contents($path);
        } else {
            $content = $response->getContent();
        }
        
        // Skip jika content kosong atau gagal baca
        if (!$content || strlen($content) < 1024) {
            $response->headers->set('X-Gzip-Status', 'Too Small');
            return $response;
        }
        
        // Kompresi dengan gzip level 6
        $compressedContent = gzencode($content, 6);
        
        // Jika gagal kompresi
        if ($compressedContent === false) {
            $response->headers->set('X-Gzip-Status', 'Compression Failed');
            return $response;
        }
        
        // Buat response baru dengan content terkompresi
        // Kita tidak bisa sekadar setContent di BinaryFileResponse karena strukturnya beda
        // Jadi kita ganti response objectnya
        $newResponse = new Response($compressedContent);
        
        // Copy headers dari response lama
        foreach ($response->headers->all() as $name => $values) {
            foreach ($values as $value) {
                $newResponse->headers->set($name, $value);
            }
        }
        
        $newResponse->headers->set('Content-Encoding', 'gzip');
        $newResponse->headers->set('Content-Length', strlen($compressedContent));
        $newResponse->headers->set('Vary', 'Accept-Encoding');
        $newResponse->headers->set('X-Gzip-Status', 'Compressed');
        
        // Hapus header content-length lama jika ada (karena size berubah)
        
        return $newResponse;
    }
    
    /**
     * Cek apakah response harus dikompresi
     */
    private function shouldCompress(Request $request, Response $response): bool
    {
        // Cek apakah client mendukung gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (strpos($acceptEncoding, 'gzip') === false) {
            return false;
        }
        
        // Skip jika sudah terkompresi
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }
        
        // Hanya kompresi content type tertentu
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'application/json',
            'application/geo+json',
            'text/plain',
            'text/html',
            'text/css',
            'application/javascript',
        ];
        
        foreach ($compressibleTypes as $type) {
            if (strpos($contentType, $type) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
