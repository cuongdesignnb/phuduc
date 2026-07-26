<?php

namespace App\Services\Admin\Operations;

class WarrantySerialNormalizer
{
    public function normalize(?string $serial): string
    {
        $serial = trim((string) $serial);
        $serial = preg_replace('/\s+/u', ' ', $serial) ?? $serial;

        return mb_strtoupper($serial);
    }
}
