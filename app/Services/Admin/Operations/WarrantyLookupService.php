<?php

namespace App\Services\Admin\Operations;

use App\Models\Warranty;
use App\Services\Storefront\PhoneNormalizer;

class WarrantyLookupService
{
    public function __construct(
        private readonly WarrantySerialNormalizer $serials,
        private readonly PhoneNormalizer $phones,
    ) {}

    public function find(string $serial, string $phone): ?Warranty
    {
        $warranty = Warranty::query()
            ->with('order:id,customer_phone')
            ->whereRaw('UPPER(serial_number) = ?', [$this->serials->normalize($serial)])
            ->first();
        if (! $warranty) {
            return null;
        }

        $expected = $warranty->customer_phone ?: $warranty->order?->customer_phone;
        if (! $expected || ! hash_equals($this->phones->normalize($expected), $this->phones->normalize($phone))) {
            return null;
        }

        return $warranty;
    }
}
