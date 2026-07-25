<?php

namespace App\Services\Admin\Operations;

use App\Models\Warranty;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class WarrantyStatusService
{
    private const LABELS = [
        'scheduled' => 'Chờ kích hoạt',
        'active' => 'Còn hiệu lực',
        'expired' => 'Hết hạn',
        'voided' => 'Đã hủy',
    ];

    public function effective(Warranty $warranty, ?CarbonImmutable $today = null): string
    {
        $today ??= CarbonImmutable::today();
        if ($warranty->status === 'voided') {
            return 'voided';
        }
        if ($warranty->activation_date?->greaterThan($today)) {
            return 'scheduled';
        }
        if ($warranty->expiration_date?->lessThan($today)) {
            return 'expired';
        }

        return 'active';
    }

    public function label(string $status): string
    {
        return self::LABELS[$status] ?? 'Đang cập nhật';
    }

    public function filter(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }
        $today = CarbonImmutable::today()->toDateString();

        return match ($status) {
            'voided' => $query->where('status', 'voided'),
            'scheduled' => $query->where('status', '!=', 'voided')->whereDate('activation_date', '>', $today),
            'expired' => $query->where('status', '!=', 'voided')->whereDate('expiration_date', '<', $today),
            'active' => $query->where('status', '!=', 'voided')->where(fn ($q) => $q->whereNull('activation_date')->orWhereDate('activation_date', '<=', $today))->where(fn ($q) => $q->whereNull('expiration_date')->orWhereDate('expiration_date', '>=', $today)),
            default => $query,
        };
    }

    public function activeForDashboard(Builder $query): Builder
    {
        $today = CarbonImmutable::today()->toDateString();

        return $query->where('status', '!=', 'voided')
            ->where(fn ($q) => $q->whereNull('activation_date')->orWhereDate('activation_date', '<=', $today))
            ->where(fn ($q) => $q->whereNull('expiration_date')->orWhereDate('expiration_date', '>=', $today));
    }
}
