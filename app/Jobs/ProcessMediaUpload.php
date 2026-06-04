<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\MediaLibrary;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProcessMediaUpload implements ShouldQueue
{
    use Queueable;

    public $mediaLibrary;

    /**
     * Create a new job instance.
     */
    public function __construct(MediaLibrary $mediaLibrary)
    {
        $this->mediaLibrary = $mediaLibrary;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $media = $this->mediaLibrary;
        $originalPath = Storage::disk('public')->path($media->file_path);

        if (!file_exists($originalPath)) {
            return;
        }

        // Only process image types
        if (strpos($media->mime_type, 'image/') !== 0 || $media->mime_type === 'image/webp') {
            return; // Not an image or already webp
        }

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($originalPath);

            $pathInfo = pathinfo($media->file_path);
            $newFilename = $pathInfo['filename'] . '_' . uniqid() . '.webp';
            $newPath = $pathInfo['dirname'] . '/' . $newFilename;
            $newFullPath = Storage::disk('public')->path($newPath);

            $image->toWebp(80)->save($newFullPath);

            // Update MediaLibrary
            $oldPath = $media->file_path;

            $media->file_path = $newPath;
            $media->file_name = $newFilename;
            $media->mime_type = 'image/webp';
            $media->size = filesize($newFullPath);
            $media->save();

            // Delete old file
            Storage::disk('public')->delete($oldPath);

        } catch (\Exception $e) {
            \Log::error('MediaConversion failed: ' . $e->getMessage());
        }
    }
}
