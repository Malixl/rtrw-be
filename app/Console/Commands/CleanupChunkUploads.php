<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupChunkUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup-chunks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up temporary chunk upload folders older than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tmpDir = 'tmp';
        
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($tmpDir)) {
            $this->info("Temporary directory {$tmpDir} does not exist. Nothing to clean.");
            return 0;
        }

        $directories = \Illuminate\Support\Facades\Storage::disk('local')->directories($tmpDir);
        $now = now()->timestamp;
        $twentyFourHours = 24 * 60 * 60;
        $deletedCount = 0;

        foreach ($directories as $directory) {
            $lastModified = \Illuminate\Support\Facades\Storage::disk('local')->lastModified($directory);

            if (($now - $lastModified) > $twentyFourHours) {
                \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory($directory);
                $this->info("Deleted old chunk folder: {$directory}");
                $deletedCount++;
            }
        }

        $this->info("Cleanup completed. Deleted {$deletedCount} folders.");

        return 0;
    }
}
