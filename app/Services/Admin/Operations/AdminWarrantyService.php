<?php

namespace App\Services\Admin\Operations;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Warranty;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\AdminPresentationService;

class AdminWarrantyService
{
    public function __construct(
        private readonly AdminPageService $pages,
        private readonly AdminPresentationService $presentation,
        private readonly AdminWarrantyPresentationService $warranties,
        private readonly WarrantyRegistrationService $registration,
        private readonly WarrantyStatusMutationService $status,
        private readonly WarrantyStatusService $effective,
        private readonly AdminOperationsQueryService $queries,
    ) {}

    public function index(User $user, array $filters): array
    {
        $query = Warranty::query()->select(['id', 'order_id', 'serial_number', 'product_name', 'customer_name', 'customer_phone', 'activation_date', 'expiration_date', 'status', 'updated_at'])->with('order:id,order_number,customer_name,customer_phone');
        $search = $filters['search'] ?? null;
        if ($search) {
            $escaped = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($query) use ($escaped): void {
                $query->where('serial_number', 'like', $escaped)->orWhere('product_name', 'like', $escaped)->orWhere('customer_name', 'like', $escaped)->orWhere('customer_phone', 'like', $escaped)->orWhereHas('order', fn ($order) => $order->where('order_number', 'like', $escaped));
            });
        }
        $query->when(($filters['mode'] ?? null) === 'order', fn ($query) => $query->whereNotNull('order_id'));
        $query->when(($filters['mode'] ?? null) === 'manual', fn ($query) => $query->whereNull('order_id'));
        $this->effective->filter($query, $filters['effective_status'] ?? null);
        if (($filters['expiring_within'] ?? null) === '30') {
            $query->whereNotNull('expiration_date')->whereDate('expiration_date', '>=', now()->toDateString())->whereDate('expiration_date', '<=', now()->addDays(30)->toDateString());
        }
        $paginator = $query->latest()->paginate(15)->withQueryString();

        return $this->pages->envelope($user, 'admin_warranties_index', 'Bảo hành', [['label' => 'Bảo hành', 'url' => route('admin.warranties.index')]], [
            'items' => $paginator->getCollection()->map(fn (Warranty $warranty): array => $this->warranties->item($warranty))->values()->all(),
            'pagination' => $this->presentation->pagination($paginator),
            'filters' => ['search' => $filters['search'] ?? '', 'effective_status' => $filters['effective_status'] ?? '', 'mode' => $filters['mode'] ?? '', 'expiring_within' => $filters['expiring_within'] ?? ''],
            'statuses' => collect(['scheduled', 'active', 'expired', 'voided'])->map(fn (string $key): array => ['key' => $key, 'label' => $this->effective->label($key)])->all(),
        ]);
    }

    public function editPage(User $user, ?Warranty $warranty): array
    {
        $warranty?->load(['order:id,order_number,customer_name,customer_phone', 'order.items:id,order_id,product_name,quantity', 'orderItem:id,order_id,product_name']);
        $module = [
            'warranty' => $warranty ? $this->warranties->item($warranty) + ['order_id' => $warranty->order_id, 'order_item_id' => $warranty->order_item_id, 'customer_name' => $warranty->customer_name, 'customer_phone' => $warranty->customer_phone, 'product_name' => $warranty->product_name] : null,
            'order_items' => $warranty?->order?->items?->map(fn (OrderItem $item): array => ['id' => $item->id, 'product_name' => $item->product_name, 'quantity' => $item->quantity])->values()->all() ?? [],
        ];

        return $this->pages->envelope($user, 'admin_warranty_edit', $warranty ? 'Sửa bảo hành' : 'Tạo bảo hành', [['label' => 'Bảo hành', 'url' => route('admin.warranties.index')], ['label' => $warranty ? 'Sửa bảo hành' : 'Tạo bảo hành', 'url' => null]], $module);
    }

    public function store(array $data): Warranty
    {
        return $this->registration->store($data);
    }

    public function update(Warranty $warranty, array $data): Warranty
    {
        return $this->registration->update($warranty, $data);
    }

    public function void(Warranty $warranty, array $data): Warranty
    {
        return $this->status->void($warranty, $data);
    }

    public function orderLookup(array $filters): array
    {
        $limit = $this->queries->boundedLimit($filters['limit'] ?? 20);
        $query = Order::query()->select(['id', 'order_number', 'customer_name', 'customer_phone'])->latest();
        if ($filters['search'] ?? null) {
            $escaped = '%'.addcslashes($filters['search'], '%_\\').'%';
            $query->where(fn ($query) => $query->where('order_number', 'like', $escaped)->orWhere('customer_name', 'like', $escaped)->orWhere('customer_phone', 'like', $escaped));
        }
        if ($filters['ids'] ?? []) {
            $query->whereIn('id', $filters['ids']);
        }

        return $query->limit($limit)->get()->map(fn (Order $order): array => ['id' => (int) $order->id, 'label' => $order->order_number.' — '.($order->customer_name ?: 'Chưa cập nhật'), 'order_number' => $order->order_number, 'customer_name' => $order->customer_name, 'customer_phone_masked' => $this->maskPhone($order->customer_phone)])->values()->all();
    }

    public function orderItems(Order $order): array
    {
        return OrderItem::query()->where('order_id', $order->id)->orderBy('id')->get(['id', 'product_name', 'quantity'])->map(fn (OrderItem $item): array => ['id' => (int) $item->id, 'product_name' => $item->product_name, 'quantity' => (int) $item->quantity])->values()->all();
    }

    private function maskPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        return strlen($phone) < 4 ? str_repeat('*', strlen($phone)) : substr($phone, 0, 2).str_repeat('*', max(0, strlen($phone) - 4)).substr($phone, -2);
    }
}
