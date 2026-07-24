<?php

namespace App\Services\Admin\Media;

use App\Models\MediaLibrary;
use Illuminate\Validation\ValidationException;

final class MediaAssetService
{
    public function requireImage(int $mediaId): MediaLibrary
    {
        $media = MediaLibrary::query()->findOrFail($mediaId);
        if (! str_starts_with(strtolower((string) $media->mime_type), 'image/')) {
            throw ValidationException::withMessages(['media_id' => 'Tệp được chọn phải là hình ảnh.']);
        }

        return $media;
    }
}
