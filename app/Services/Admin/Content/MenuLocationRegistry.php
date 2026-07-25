<?php

namespace App\Services\Admin\Content;

final class MenuLocationRegistry
{
    /** @return array<string, array{label: string}> */
    public static function all(): array
    {
        return ['header' => ['label' => 'Đầu trang'], 'footer' => ['label' => 'Chân trang']];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }
}
