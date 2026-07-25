<?php

namespace App\Services\Admin\Operations;

class AdminOperationsQueryService
{
    public function likePattern(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : '%'.addcslashes($value, '%_\\').'%';
    }

    public function boundedLimit(mixed $value, int $maximum = 20): int
    {
        return min($maximum, max(1, (int) ($value ?: $maximum)));
    }
}
