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
    /** @return array{path: string, mime_type: string, size: int, file_name: string} */
    public function store(UploadedFile $file, string $directory): array
    {
        $mime = (string) $file->getMimeType();
        $convert = in_array($mime, ['image/jpeg', 'image/png'], true);
        $extension = $convert ? 'webp' : (strtolower($file->extension()) ?: 'bin');
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.'.$extension;

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

            return ['path' => $path, 'mime_type' => $mime, 'size' => $size, 'file_name' => $fileName];
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
            throw new \RuntimeException('Không thể sao chép Media.');
        }

        return $path;
    }

    /** @return array{path: string, mime_type: string, size: int, file_name: string} */
    public function storeGenerated(string $contents, string $originalName, string $directory): array
    {
        $extension = 'webp';
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'ai-image';
        $path = trim($directory, '/').'/'.$baseName.'-'.Str::uuid().'.'.$extension;

        try {
            $encoded = (new ImageManager(new Driver))->read($contents)->toWebp(82)->toString();
            Storage::disk('public')->put($path, $encoded);

            return [
                'path' => $path,
                'mime_type' => 'image/webp',
                'size' => strlen($encoded),
                'file_name' => $baseName.'.webp',
            ];
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }
}
