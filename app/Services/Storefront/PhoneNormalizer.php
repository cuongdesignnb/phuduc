<?php

namespace App\Services\Storefront;

class PhoneNormalizer
{
    public function normalize(?string $value): string
    {
        $phone = preg_replace('/[\s.()\-]+/', '', trim((string) $value)) ?? '';

        if (str_starts_with($phone, '+84')) {
            $phone = '0'.substr($phone, 3);
        } elseif (str_starts_with($phone, '84')) {
            $phone = '0'.substr($phone, 2);
        }

        return $phone;
    }
}
