<?php

namespace App\Services\Admin\Operations;

use App\Models\Order;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPresentationService;

class AdminOrderPresentationService
{
    public function __construct(
        private readonly AdminPresentationService $presentation,
        private readonly OrderStatusRegistry $statuses,
        private readonly AdminConcurrencyService $concurrency,
    ) {}

    public function index(Order $order): array
    {
        return [
            'id' => (int) $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email,
            'items_count' => (int) ($order->items_count ?? 0),
            'warranties_count' => (int) ($order->warranties_count ?? 0),
            'total' => (int) round((float) $order->total_amount),
            'total_display' => $this->presentation->money($order->total_amount)['display'],
            'status' => ['key' => $order->status, 'label' => $this->statuses->label($order->status)],
            'created_at' => $order->created_at?->toIso8601String(),
            'created_at_display' => $this->presentation->date($order->created_at),
            'detail_url' => route('admin.orders.show', $order),
        ];
    }

    public function detail(Order $order): array
    {
        $items = $order->items->map(function ($item): array {
            $unit = (int) round((float) $item->price);
            $subtotal = (int) round((float) $item->total);
            $current = $item->product;

            return [
                'id' => (int) $item->id,
                'product_name' => $item->product_name,
                'product_id' => $item->product_id !== null ? (int) $item->product_id : null,
                'quantity' => (int) $item->quantity,
                'unit_price' => $unit,
                'unit_price_display' => $this->presentation->money($unit)['display'],
                'subtotal' => $subtotal,
                'subtotal_display' => $this->presentation->money($subtotal)['display'],
                'current_product_url' => $current ? route('admin.products.edit', $current) : null,
            ];
        })->values()->all();
        $computed = array_sum(array_column($items, 'subtotal'));
        $stored = (int) round((float) $order->total_amount);

        return [
            'id' => (int) $order->id,
            'order_number' => $order->order_number,
            'customer' => [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email,
            ],
            'shipping_address' => $order->shipping_address,
            'notes' => $order->notes,
            'items' => $items,
            'warranties' => $order->warranties->map(fn ($warranty): array => [
                'id' => (int) $warranty->id,
                'serial_number' => $warranty->serial_number,
                'product_name' => $warranty->product_name,
                'status' => $warranty->status,
            ])->values()->all(),
            'total' => $stored,
            'total_display' => $this->presentation->money($stored)['display'],
            'total_parity' => [
                'computed_items_total' => $computed,
                'computed_items_total_display' => $this->presentation->money($computed)['display'],
                'stored_order_total' => $stored,
                'is_equal' => $computed === $stored,
                'warning' => $computed === $stored ? null : 'Tổng tiền đơn hàng khác tổng các dòng sản phẩm. Vui lòng kiểm tra dữ liệu lịch sử.',
            ],
            'status' => ['key' => $order->status, 'label' => $this->statuses->label($order->status)],
            'allowed_next_statuses' => collect($this->statuses->allowedNext($order->status))->map(fn (string $status): array => ['key' => $status, 'label' => $this->statuses->label($status)])->values()->all(),
            'is_terminal' => $this->statuses->isTerminal($order->status),
            'version' => $this->concurrency->version($order),
            'status_history' => $order->statusHistories->map(fn ($history): array => [
                'from_status' => ['key' => $history->from_status, 'label' => $this->statuses->label($history->from_status)],
                'to_status' => ['key' => $history->to_status, 'label' => $this->statuses->label($history->to_status)],
                'reason' => $history->reason,
                'created_at_display' => $this->presentation->date($history->created_at),
            ])->values()->all(),
            'created_at_display' => $this->presentation->date($order->created_at),
        ];
    }
}
