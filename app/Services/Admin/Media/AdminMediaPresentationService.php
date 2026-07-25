<?php

namespace App\Services\Admin\Media;

use App\Models\MediaLibrary;
use App\Services\Admin\AdminPresentationService;
use App\Services\Storefront\MediaUrlService;
use App\Support\Media\ImageMimeTypes;

class AdminMediaPresentationService
{
    public function __construct(
        private readonly AdminPresentationService $presentation,
        private readonly MediaUrlService $mediaUrl,
    ) {}

    /** @return array<string, mixed> */
    public function item(MediaLibrary $media, array $references = []): array
    {
        $size = (int) $media->size;

        return [
            'id' => $media->id,
            'file_name' => $media->file_name,
            'alt_text' => $media->alt_text,
            'mime_type' => $media->mime_type,
            'media_type' => ImageMimeTypes::isAllowed($media->mime_type) ? 'image' : 'file',
            'size' => $size,
            'size_display' => $this->size($size),
            'url' => $this->mediaUrl->resolve($media->file_path),
            'thumbnail_url' => ImageMimeTypes::isAllowed($media->mime_type) ? $this->mediaUrl->resolve($media->file_path) : null,
            'created_at_display' => $this->presentation->date($media->created_at),
            'updated_at_display' => $this->presentation->date($media->updated_at),
            'references_count' => array_sum(array_column($references, 'count')),
            'reference_types' => $references,
            'can_delete' => $references === [],
            'edit_url' => route('admin.media.update', $media),
            'delete_url' => route('admin.media.destroy', $media),
        ];
    }

    /** @return array<string, mixed> */
    public function pickerItem(MediaLibrary $media): array
    {
        return [
            'id' => $media->id,
            'file_name' => $media->file_name,
            'alt_text' => $media->alt_text,
            'mime_type' => $media->mime_type,
            'media_type' => ImageMimeTypes::isAllowed($media->mime_type) ? 'image' : 'file',
            'url' => $this->mediaUrl->resolve($media->file_path),
            'thumbnail_url' => ImageMimeTypes::isAllowed($media->mime_type) ? $this->mediaUrl->resolve($media->file_path) : null,
        ];
    }

    private function size(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1, ',', '.').' KB';
        }

        return number_format($bytes / (1024 * 1024), 1, ',', '.').' MB';
    }
}
