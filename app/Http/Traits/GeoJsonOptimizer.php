<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait GeoJsonOptimizer
{
    /**
     * Store a GeoJSON file as-is without any modification.
     * Preserves original coordinate precision from QGIS.
     *
     * @param UploadedFile $file
     * @param string $folderPath
     * @return string
     * @throws \Exception
     */
    public function optimizeAndStore(UploadedFile $file, string $folderPath): string
    {
        // 1. Validate file extension quickly
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'geojson' && $extension !== 'json') {
            throw new \Exception("Invalid file type. Only .geojson or .json allowed.");
        }

        // 2. Validate it's likely a JSON file efficiently (read first few bytes)
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle) {
            $firstChunk = fread($handle, 1024); // read first 1KB
            fclose($handle);

            // Check if it looks like a JSON object or array
            $trimmed = trim($firstChunk);
            if (empty($trimmed) || ($trimmed[0] !== '{' && $trimmed[0] !== '[')) {
                throw new \Exception("File does not appear to be a valid JSON/GeoJSON structure.");
            }
        } else {
            throw new \Exception("Could not read the uploaded file.");
        }

        // 3. Generate unique filename
        $filename = Str::random(20) . '-' . time() . '.geojson';
        $path = $folderPath . '/' . $filename;

        // 4. Store using stream to avoid memory exhaustion (putFile handles streams automatically)
        $storedPath = Storage::disk('public')->putFileAs($folderPath, $file, $filename);

        if (!$storedPath) {
            throw new \Exception("Failed to save the file to storage.");
        }

        // 5. Return relative file path string (for the database)
        return $path;
    }
}
