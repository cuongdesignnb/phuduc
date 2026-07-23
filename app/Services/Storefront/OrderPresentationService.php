<?php

namespace App\Services\Storefront;

use App\Models\Order;

class OrderPresentationService
{
    public function __construct(private readonly CartPresentationService $cartPresentation) {}

    public function success(Order $order): array
    {
        return array_merge($this->public($order, true), [
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'shipping_address' => $order->shipping_address,
        ]);
    }

    public function lookup(Order $order): array
    {
        return $this->public($order, false);
    }

    private function public(Order $order, bool $includeCustomer): array
    {
        return [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_display' => $this->status($order->status),
            'created_at_display' => $order->created_at?->format('d/m/Y H:i'),
            'total_display' => $this->cartPresentation->money((int) round((float) $order->total_amount)),
            'items' => $order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'total_display' => $this->cartPresentation->money((int) round((float) $item->total)),
            ])->values()->all(),
        ];
    }

    private function status(string $status): string
    {
        return [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipping' => 'Đang giao hàng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ][$status] ?? 'Đang cập nhật';
    }
}
