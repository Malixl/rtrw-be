<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessGeoJsonJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes

    /**
     * The model class name (e.g., 'App\Models\DataSpasial')
     */
    protected string $modelClass;

    /**
     * The model ID
     */
    protected string $modelId;

    /**
     * The temporary file path
     */
    protected string $tempFilePath;

    /**
     * The target folder path
     */
    protected string $targetFolder;

    /**
     * Create a new job instance.
     */
    public function __construct(string $modelClass, string $modelId, string $tempFilePath, string $targetFolder)
    {
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;
        $this->tempFilePath = $tempFilePath;
        $this->targetFolder = $targetFolder;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to processing
            $model = $this->modelClass::find($this->modelId);
            if (!$model) {
                Log::error("ProcessGeoJsonJob: Model not found", [
                    'class' => $this->modelClass,
                    'id' => $this->modelId
                ]);
                return;
            }

            $model->processing_status = 'processing';
            $model->save();

            // Get the temp file content
            if (!Storage::disk('public')->exists($this->tempFilePath)) {
                throw new \Exception("Temp file not found: {$this->tempFilePath}");
            }

            $content = Storage::disk('public')->get($this->tempFilePath);
            $json = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON file: " . json_last_error_msg());
            }

            // Optimize geometry (round coordinates)
            $this->optimizeGeometry($json);

            // Encode back to JSON
            $optimizedContent = json_encode($json);

            // Generate final filename
            $filename = md5($optimizedContent . time()) . '.geojson';
            $finalPath = $this->targetFolder . '/' . $filename;

            // Store optimized content
            Storage::disk('public')->put($finalPath, $optimizedContent);

            // Delete temp file
            Storage::disk('public')->delete($this->tempFilePath);

            // Update model with final path and status
            $model->geojson_file = $finalPath;
            $model->processing_status = 'completed';
            $model->processing_error = null;
            $model->save();

            Log::info("ProcessGeoJsonJob: Successfully processed", [
                'class' => $this->modelClass,
                'id' => $this->modelId,
                'file' => $finalPath
            ]);

        } catch (\Exception $e) {
            Log::error("ProcessGeoJsonJob: Failed", [
                'class' => $this->modelClass,
                'id' => $this->modelId,
                'error' => $e->getMessage()
            ]);

            // Update status to failed
            $model = $this->modelClass::find($this->modelId);
            if ($model) {
                $model->processing_status = 'failed';
                $model->processing_error = $e->getMessage();
                $model->save();
            }

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Recursively find 'coordinates' keys and round their values.
     */
    private function optimizeGeometry(array &$data): void
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
     */
    private function roundCoordinates(array $coords): array
    {
        if (empty($coords)) {
            return $coords;
        }

        // Check if it is a coordinate point (array of numbers)
        if (isset($coords[0]) && is_numeric($coords[0])) {
            return array_map(function ($val) {
                return is_numeric($val) ? round((float)$val, 5) : $val;
            }, $coords);
        }

        // If it's an array of arrays, recurse
        foreach ($coords as &$subCoords) {
            if (is_array($subCoords)) {
                $subCoords = $this->roundCoordinates($subCoords);
            }
        }

        return $coords;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessGeoJsonJob: Job failed permanently", [
            'class' => $this->modelClass,
            'id' => $this->modelId,
            'error' => $exception->getMessage()
        ]);

        // Update status to failed
        $model = $this->modelClass::find($this->modelId);
        if ($model) {
            $model->processing_status = 'failed';
            $model->processing_error = $exception->getMessage();
            $model->save();
        }
    }
}
