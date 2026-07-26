<?php

namespace App\Services\Storefront;

use App\Models\Warranty;
use App\Services\Admin\Operations\WarrantyStatusService;

class WarrantyPresentationService
{
    public function __construct(private readonly WarrantyStatusService $statuses) {}

    public function present(Warranty $warranty): array
    {
        $effective = $this->statuses->effective($warranty);

        return [
            'product_name' => $warranty->product_name,
            'serial_number' => $warranty->serial_number,
            'activation_date_display' => $warranty->activation_date?->format('d/m/Y') ?? 'Chưa cập nhật',
            'expiration_date_display' => $warranty->expiration_date?->format('d/m/Y') ?? 'Chưa cập nhật',
            'status' => $effective,
            'status_display' => $this->statuses->label($effective),
            'stored_status' => $warranty->status,
        ];
    }
}
