<?php

namespace App\Services\Admin;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminPresentationService
{
    private const STATUS_LABELS = [
        'pending' => 'Chờ xử lý',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
        'active' => 'Đang hoạt động',
        'inactive' => 'Tạm dừng',
    ];

    public function money(mixed $value): array
    {
        $amount = (int) round((float) $value);

        return ['value' => $amount, 'display' => number_format($amount, 0, ',', '.').' ₫'];
    }

    public function date(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('d/m/Y H:i');
        }

        return filled($value) ? date('d/m/Y H:i', strtotime((string) $value)) : '';
    }

    public function status(string $status): array
    {
        return ['key' => $status, 'label' => self::STATUS_LABELS[$status] ?? $status];
    }

    public function statusLabel(string $status): string
    {
        return $this->status($status)['label'];
    }

    /**
     * @return array<string, mixed>
     */
    public function pagination(LengthAwarePaginator $paginator): array
    {
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $links = [[
            'key' => 'previous',
            'label' => 'Trước',
            'url' => $paginator->previousPageUrl(),
            'active' => false,
            'disabled' => $currentPage <= 1,
        ]];

        foreach ($paginator->getUrlRange(1, $lastPage) as $page => $url) {
            $links[] = [
                'key' => 'page-'.$page,
                'label' => (string) $page,
                'url' => $url,
                'active' => $page === $currentPage,
                'disabled' => false,
            ];
        }

        $links[] = [
            'key' => 'next',
            'label' => 'Sau',
            'url' => $paginator->nextPageUrl(),
            'active' => false,
            'disabled' => $currentPage >= $lastPage,
        ];

        return [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'links' => $links,
        ];
    }
}
