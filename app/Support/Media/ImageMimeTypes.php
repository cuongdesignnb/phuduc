<?php

namespace App\Support\Media;

final class ImageMimeTypes
{
    public const ALLOWLIST = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    public static function isAllowed(?string $mimeType): bool
    {
        return in_array(strtolower((string) $mimeType), self::ALLOWLIST, true);
    }
}
