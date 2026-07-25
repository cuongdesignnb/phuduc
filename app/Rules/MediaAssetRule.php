<?php

namespace App\Rules;

use App\Support\Media\ImageMimeTypes;
use Illuminate\Validation\Rule;

final class MediaAssetRule
{
    public static function image(): object
    {
        return Rule::exists('media_libraries', 'id')->where(fn ($query) => $query->whereIn('mime_type', ImageMimeTypes::ALLOWLIST));
    }
}
