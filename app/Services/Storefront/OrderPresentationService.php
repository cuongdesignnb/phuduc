<?php

namespace App\Services\Storefront;

use App\Models\Order;

class OrderPresentationService
{
    public function __construct(private readonly CartPresentationService $cartPresentation) {}

    public function success(Order $order): array
    {
        return array_merge($this->public($order), [
            'customer' => [
                'name' => $order->customer_name,
                'phone_masked' => $this->maskPhone($order->customer_phone),
            ],
        ]);
    }

    public function lookup(Order $order): array
    {
        return $this->public($order);
    }

    private function public(Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_display' => $this->status($order->status),
            'created_at' => $order->created_at?->toIso8601String(),
            'created_at_display' => $order->created_at?->format('d/m/Y H:i'),
            'items' => $order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (int) round((float) $item->price),
                'unit_price_display' => $this->cartPresentation->money((int) round((float) $item->price)),
                'subtotal' => (int) round((float) $item->total),
                'subtotal_display' => $this->cartPresentation->money((int) round((float) $item->total)),
                'total_display' => $this->cartPresentation->money((int) round((float) $item->total)),
            ])->values()->all(),
            'total' => (int) round((float) $order->total_amount),
            'total_display' => $this->cartPresentation->money((int) round((float) $order->total_amount)),
        ];
    }

    private function maskPhone(?string $phone): string
    {
        $phone = (string) $phone;

        if (strlen($phone) < 4) {
            return str_repeat('*', strlen($phone));
        }

        return substr($phone, 0, 2).str_repeat('*', max(0, strlen($phone) - 4)).substr($phone, -2);
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
