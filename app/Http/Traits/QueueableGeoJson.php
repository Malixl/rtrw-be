<?php

namespace App\Http\Traits;

use App\Jobs\ProcessGeoJsonJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait QueueableGeoJson
{
    /**
     * Size threshold in bytes (5MB). Files larger than this will be processed via queue.
     */
    protected int $queueThreshold = 5 * 1024 * 1024; // 5MB

    /**
     * Store file temporarily and dispatch job for processing if file is large.
     * For small files, process synchronously for immediate response.
     *
     * @param UploadedFile $file
     * @param string $folderPath
     * @param string $modelClass
     * @param string $modelId
     * @return array ['path' => string, 'queued' => bool]
     */
    public function storeAndOptimizeGeoJson(UploadedFile $file, string $folderPath, string $modelClass, string $modelId): array
    {
        $fileSize = $file->getSize();

        if ($fileSize > $this->queueThreshold) {
            // Large file: store temporarily and process via queue
            return $this->storeForQueueProcessing($file, $folderPath, $modelClass, $modelId);
        }

        // Small file: process synchronously using existing GeoJsonOptimizer trait
        $path = $this->optimizeAndStore($file, $folderPath);
        return [
            'path' => $path,
            'queued' => false,
            'status' => 'completed'
        ];
    }

    /**
     * Store file temporarily and dispatch background job.
     */
    protected function storeForQueueProcessing(UploadedFile $file, string $folderPath, string $modelClass, string $modelId): array
    {
        // Generate temp filename
        $tempFilename = 'temp/' . Str::random(20) . '-' . time() . '.geojson';

        // Store the uploaded file as-is (no optimization yet)
        Storage::disk('public')->put($tempFilename, file_get_contents($file->getRealPath()));

        // Dispatch job to process in background
        ProcessGeoJsonJob::dispatch($modelClass, $modelId, $tempFilename, $folderPath);

        return [
            'path' => $tempFilename, // Temporary path, will be updated by job
            'queued' => true,
            'status' => 'pending'
        ];
    }

    /**
     * Check if file should be processed via queue based on size.
     */
    public function shouldQueueFile(UploadedFile $file): bool
    {
        return $file->getSize() > $this->queueThreshold;
    }
}
