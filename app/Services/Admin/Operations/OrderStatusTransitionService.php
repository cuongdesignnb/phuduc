<?php

namespace App\Services\Admin\Operations;

use App\Models\Order;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use Illuminate\Support\Facades\DB;

class OrderStatusTransitionService
{
    public function __construct(
        private readonly OrderStatusRegistry $registry,
        private readonly OrderStockRestorationService $stock,
        private readonly AdminConcurrencyService $concurrency,
    ) {}

    public function transition(Order $order, User $actor, array $data): array
    {
        return DB::transaction(function () use ($order, $actor, $data): array {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            $this->concurrency->assertVersion($data['version'] ?? null, $locked, 'Đơn hàng đã được cập nhật ở phiên khác. Vui lòng tải lại.');
            $from = (string) $locked->status;
            $to = (string) $data['status'];
            $this->registry->assertTransition($from, $to);

            if ($from === $to) {
                return ['order' => $locked->load(['items', 'warranties', 'statusHistories']), 'unresolved_stock_lines' => [], 'changed' => false];
            }

            $unresolved = [];
            if ($to === 'cancelled' && in_array($from, ['pending', 'processing'], true)) {
                $unresolved = $this->stock->restore($locked);
            }

            $locked->update(['status' => $to]);
            $locked->statusHistories()->create([
                'actor_id' => $actor->id,
                'from_status' => $from,
                'to_status' => $to,
                'reason' => $data['reason'] ?? null,
            ]);

            return ['order' => $locked->refresh()->load(['items', 'warranties', 'statusHistories']), 'unresolved_stock_lines' => $unresolved, 'changed' => true];
        });
    }
}
