<?php

namespace App\Services\Admin\Operations;

use App\Models\Order;
use App\Models\User;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\AdminPresentationService;

class AdminOrderService
{
    public function __construct(
        private readonly AdminPageService $pages,
        private readonly AdminPresentationService $presentation,
        private readonly AdminOrderPresentationService $orders,
        private readonly OrderStatusRegistry $statuses,
        private readonly OrderStatusTransitionService $transitions,
    ) {}

    public function index(User $user, array $filters): array
    {
        $query = Order::query()
            ->select(['id', 'order_number', 'customer_name', 'customer_phone', 'customer_email', 'total_amount', 'status', 'created_at'])
            ->withCount(['items', 'warranties']);
        $search = $filters['search'] ?? null;
        if ($search) {
            $escaped = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($query) use ($escaped): void {
                $query->where('order_number', 'like', $escaped)
                    ->orWhere('customer_name', 'like', $escaped)
                    ->orWhere('customer_phone', 'like', $escaped)
                    ->orWhere('customer_email', 'like', $escaped);
            });
        }
        $query->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status));
        $query->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date));
        $query->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
        $sort = $filters['sort'] ?? 'latest';
        $orderBy = in_array($sort, ['total_high', 'total_low'], true) ? 'total_amount' : 'created_at';
        $direction = in_array($sort, ['oldest', 'total_low'], true) ? 'asc' : 'desc';
        $query->orderBy($orderBy, $direction);
        $paginator = $query->paginate(15)->withQueryString();

        return $this->pages->envelope($user, 'admin_orders_index', 'Đơn hàng', [['label' => 'Đơn hàng', 'url' => route('admin.orders.index')]], [
            'items' => $paginator->getCollection()->map(fn (Order $order): array => $this->orders->index($order))->values()->all(),
            'pagination' => $this->presentation->pagination($paginator),
            'filters' => [
                'search' => $filters['search'] ?? '', 'status' => $filters['status'] ?? '',
                'date_from' => $filters['date_from'] ?? '', 'date_to' => $filters['date_to'] ?? '',
                'sort' => $sort,
            ],
            'statuses' => $this->statuses->options(),
        ]);
    }

    public function detail(User $user, Order $order): array
    {
        $order->load([
            'items.product:id,slug,status',
            'warranties:id,order_id,serial_number,product_name,status',
            'statusHistories' => fn ($query) => $query->latest('created_at')->latest('id'),
        ]);

        return $this->pages->envelope($user, 'admin_order_detail', 'Chi tiết đơn hàng', [
            ['label' => 'Đơn hàng', 'url' => route('admin.orders.index')],
            ['label' => $order->order_number, 'url' => route('admin.orders.show', $order)],
        ], ['order' => $this->orders->detail($order), 'statuses' => $this->statuses->options()]);
    }

    public function updateStatus(Order $order, User $user, array $data): array
    {
        return $this->transitions->transition($order, $user, $data);
    }
}
