<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait GeoJsonOptimizer
{
    /**
     * Maximum file size in bytes for optimization (500KB)
     * Files larger than this will be stored without optimization to prevent timeout
     */
    protected int $maxOptimizeSize = 500 * 1024; // 500KB

    /**
     * Optimize (round coordinates) and store a GeoJSON file.
     * For large files, skip optimization to prevent timeout.
     *
     * @param UploadedFile $file
     * @param string $folderPath
     * @return string
     * @throws \Exception
     */
    public function optimizeAndStore(UploadedFile $file, string $folderPath): string
    {
        $fileSize = $file->getSize();
        
        // Generate unique filename
        $filename = md5(uniqid() . time()) . '.geojson';
        $path = $folderPath . '/' . $filename;

        // For large files, skip optimization to prevent timeout/memory issues
        if ($fileSize > $this->maxOptimizeSize) {
            // Store file directly without optimization
            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
            return $path;
        }

        // For smaller files, apply optimization
        $content = file_get_contents($file->getRealPath());
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON file uploaded.");
        }

        // Recursively traverse to find 'coordinates' and round them
        $this->optimizeGeometry($json);

        // Encode back to JSON string
        $optimizedContent = json_encode($json);

        // Store optimized content
        Storage::disk('public')->put($path, $optimizedContent);

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
