<?php

namespace App\Rules;

use Illuminate\Validation\Rule;

final class MediaAssetRule
{
    public static function image(): object
    {
        return Rule::exists('media_libraries', 'id')->where(
            fn ($query) => $query->where('mime_type', 'like', 'image/%')
        );
    }
}
