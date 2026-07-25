<?php

namespace App\Services\Admin\Media;

use App\Models\MediaLibrary;
use App\Support\Media\ImageMimeTypes;
use Illuminate\Validation\ValidationException;

final class MediaAssetService
{
    public function requireImage(int $mediaId): MediaLibrary
    {
        $media = MediaLibrary::query()->findOrFail($mediaId);
        if (! ImageMimeTypes::isAllowed($media->mime_type)) {
            throw ValidationException::withMessages(['media_id' => 'Tệp được chọn phải là hình ảnh.']);
        }

        return $media;
    }
}
