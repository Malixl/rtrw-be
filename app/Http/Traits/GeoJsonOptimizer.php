<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait GeoJsonOptimizer
{
    /**
     * Optimize (round coordinates) and store a GeoJSON file.
     *
     * @param UploadedFile $file
     * @param string $folderPath
     * @return string
     * @throws \Exception
     */
    public function optimizeAndStore(UploadedFile $file, string $folderPath): string
    {
        // 1. Decode the uploaded file content
        $content = file_get_contents($file->getRealPath());
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback or throw exception. Since we expect valid GeoJSON, we throw.
            throw new \Exception("Invalid JSON file uploaded.");
        }

        // 2. Recursively traverse to find 'coordinates' and round them
        $this->optimizeGeometry($json);

        // 3. Encode back to JSON string
        // JSON_PRESERVE_ZERO_FRACTION is useful if we want 1.0 to stay 1.0, but round() returns float.
        $optimizedContent = json_encode($json);

        // 4. Generate unique filename
        // Using hash of content + timestamp ensures uniqueness
        $filename = md5($optimizedContent . time()) . '.geojson';
        $path = $folderPath . '/' . $filename;

        // 5. Store optimized content
        Storage::disk('public')->put($path, $optimizedContent);

        // 6. Return relative file path string (for the database)
        return $path;
    }

    /**
     * Recursively find 'coordinates' keys and round their values.
     *
     * @param array $data
     */
    private function optimizeGeometry(array &$data)
    {
        foreach ($data as $key => &$value) {
            if ($key === 'coordinates' && is_array($value)) {
                $value = $this->roundCoordinates($value);
            } elseif (is_array($value)) {
                $this->optimizeGeometry($value);
            }
        }
    }

    /**
     * Recursively round coordinates to 5 decimal places.
     * Handles nested arrays for various geometry types (Point, Polygon, MultiPolygon, etc).
     *
     * @param array $coords
     * @return array
     */
    private function roundCoordinates(array $coords)
    {
        if (empty($coords)) {
            return $coords;
        }

        // Check if it is a coordinate point (array of numbers) like [x, y] or [x, y, z]
        // Note: checking first element is numeric is usually sufficient for GeoJSON
        if (isset($coords[0]) && is_numeric($coords[0])) {
            return array_map(function ($val) {
                return is_numeric($val) ? round((float)$val, 5) : $val;
            }, $coords);
        }

        // If it's an array of arrays (LineString, Polygon, etc.), recurse
        foreach ($coords as &$subCoords) {
            if (is_array($subCoords)) {
                $subCoords = $this->roundCoordinates($subCoords);
            }
        }

        return $coords;
    }
}
