<?php

namespace App\Services\Admin\Operations;

use Illuminate\Validation\ValidationException;

class OrderStatusRegistry
{
    private const LABELS = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    private const TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['shipping', 'cancelled'],
        'shipping' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public static function values(): array
    {
        return array_keys(self::LABELS);
    }

    public function options(): array
    {
        return collect(self::LABELS)->map(fn (string $label, string $key): array => ['key' => $key, 'label' => $label])->values()->all();
    }

    public function label(string $status): string
    {
        return self::LABELS[$status] ?? 'Đang cập nhật';
    }

    public function allowedNext(string $status): array
    {
        return self::TRANSITIONS[$status] ?? [];
    }

    public function isTerminal(string $status): bool
    {
        return $this->allowedNext($status) === [];
    }

    public function assertTransition(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        if (! in_array($to, $this->allowedNext($from), true)) {
            throw ValidationException::withMessages(['status' => 'Không thể chuyển trạng thái đơn hàng theo quy trình hiện tại.']);
        }
    }
}
