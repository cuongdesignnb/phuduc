<?php

namespace App\Services\Admin\Content;

final class MenuLocationRegistry
{
    /** @return array<string, array{label: string}> */
    public static function all(): array { return ['header' => ['label' => 'Header'], 'footer' => ['label' => 'Footer']]; }
    public static function keys(): array { return array_keys(self::all()); }
}
