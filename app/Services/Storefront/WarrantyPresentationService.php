<?php

namespace App\Services\Storefront;

use App\Models\Warranty;

class WarrantyPresentationService
{
    public function present(Warranty $warranty): array
    {
        return [
            'product_name' => $warranty->product_name,
            'serial_number' => $warranty->serial_number,
            'activation_date_display' => $warranty->activation_date?->format('d/m/Y') ?? 'Chưa cập nhật',
            'expiration_date_display' => $warranty->expiration_date?->format('d/m/Y') ?? 'Chưa cập nhật',
            'status' => $warranty->status,
            'status_display' => [
                'active' => 'Còn hiệu lực',
                'expired' => 'Hết hạn',
                'voided' => 'Đã hủy',
            ][$warranty->status] ?? 'Đang cập nhật',
        ];
    }
}
