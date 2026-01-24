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
        
        // Skip jika sudah terkompresi atau client tidak mendukung gzip
        if (!$this->shouldCompress($request, $response)) {
            return $response;
        }
        
        // Dapatkan content
        $content = $response->getContent();
        
        // Skip jika content terlalu kecil (overhead gzip tidak worth it)
        if (strlen($content) < 1024) {
            return $response;
        }
        
        // Kompresi dengan gzip level 6 (balance antara kecepatan dan kompresi)
        $compressedContent = gzencode($content, 6);
        
        // Set response dengan content terkompresi
        $response->setContent($compressedContent);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', strlen($compressedContent));
        $response->headers->set('Vary', 'Accept-Encoding');
        
        return $response;
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
