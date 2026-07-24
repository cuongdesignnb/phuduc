<?php

namespace App\Services\Admin\Media;

use App\Models\MediaLibrary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AdminImageStorageService
{
    /** @return array{path: string, mime_type: string, size: int} */
    public function store(UploadedFile $file, string $directory): array
    {
        $mime = (string) $file->getMimeType();
        $convert = in_array($mime, ['image/jpeg', 'image/png'], true);
        $extension = $convert ? 'webp' : (strtolower($file->extension()) ?: 'bin');
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        try {
            if ($convert) {
                $contents = (new ImageManager(new Driver))->read($file->getRealPath())->toWebp(82)->toString();
                Storage::disk('public')->put($path, $contents);
                $mime = 'image/webp';
                $size = strlen($contents);
            } else {
                $path = (string) $file->storeAs(trim($directory, '/'), basename($path), 'public');
                $size = (int) $file->getSize();
            }

            return ['path' => $path, 'mime_type' => $mime, 'size' => $size];
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    public function copyMedia(MediaLibrary $media, string $directory): string
    {
        $extension = strtolower(pathinfo($media->file_path, PATHINFO_EXTENSION) ?: 'bin');
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        if (! Storage::disk('public')->copy($media->file_path, $path)) {
            throw new \RuntimeException('Media copy failed.');
        }

        return $path;
    }
}
