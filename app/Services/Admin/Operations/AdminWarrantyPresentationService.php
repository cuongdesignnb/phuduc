<?php

namespace App\Services\Admin\Operations;

use App\Models\Warranty;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPresentationService;

class AdminWarrantyPresentationService
{
    public function __construct(
        private readonly AdminPresentationService $presentation,
        private readonly AdminConcurrencyService $concurrency,
        private readonly WarrantyStatusService $statuses,
    ) {}

    public function item(Warranty $warranty): array
    {
        $effective = $this->statuses->effective($warranty);

        return [
            'id' => (int) $warranty->id,
            'serial' => $warranty->serial_number,
            'product_name' => $warranty->product_name,
            'customer' => ['name' => $warranty->customer_name ?: $warranty->order?->customer_name, 'phone_masked' => $this->maskPhone($warranty->customer_phone ?: $warranty->order?->customer_phone)],
            'source' => ['key' => $warranty->order_id ? 'order' : 'manual', 'label' => $warranty->order_id ? 'Theo đơn hàng' : 'Thủ công'],
            'order' => $warranty->order ? ['id' => (int) $warranty->order->id, 'order_number' => $warranty->order->order_number, 'url' => route('admin.orders.show', $warranty->order)] : null,
            'activation_date' => $warranty->activation_date?->toDateString(),
            'activation_date_display' => $warranty->activation_date?->format('d/m/Y') ?? 'Chưa cập nhật',
            'expiration_date' => $warranty->expiration_date?->toDateString(),
            'expiration_date_display' => $warranty->expiration_date?->format('d/m/Y') ?? 'Không thời hạn',
            'stored_status' => $warranty->status,
            'effective_status' => $effective,
            'effective_status_label' => $this->statuses->label($effective),
            'edit_url' => route('admin.warranties.edit', $warranty),
            'version' => $this->concurrency->version($warranty),
            'allowed_actions' => ['void' => $warranty->status !== 'voided'],
        ];
    }

    private function maskPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        return strlen($phone) < 4 ? str_repeat('*', strlen($phone)) : substr($phone, 0, 2).str_repeat('*', max(0, strlen($phone) - 4)).substr($phone, -2);
    }
}
