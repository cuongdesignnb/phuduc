<?php

namespace App\Services\Admin\Operations;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Warranty;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Storefront\PhoneNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarrantyRegistrationService
{
    public function __construct(
        private readonly WarrantySerialNormalizer $serials,
        private readonly PhoneNormalizer $phones,
        private readonly AdminConcurrencyService $concurrency,
    ) {}

    public function store(array $data): Warranty
    {
        return DB::transaction(function () use ($data): Warranty {
            $payload = $this->payload($data);
            $this->assertUnique($payload['serial_number']);

            return Warranty::create($payload)->refresh();
        });
    }

    public function update(Warranty $warranty, array $data): Warranty
    {
        return DB::transaction(function () use ($warranty, $data): Warranty {
            $locked = Warranty::query()->lockForUpdate()->findOrFail($warranty->id);
            $this->concurrency->assertVersion($data['version'] ?? null, $locked, 'Bảo hành đã được cập nhật ở phiên khác. Vui lòng tải lại.');
            $payload = $this->payload($data, $locked);
            $this->assertUnique($payload['serial_number'], $locked->id);
            unset($payload['version']);
            $locked->update($payload);

            return $locked->refresh();
        });
    }

    private function payload(array $data, ?Warranty $current = null): array
    {
        $mode = $data['mode'] ?? (($data['order_id'] ?? null) ? 'order' : 'manual');
        $serial = $this->serials->normalize($data['serial_number'] ?? null);
        if ($serial === '' || preg_match('/[\x00-\x1F\x7F]/u', $serial)) {
            throw ValidationException::withMessages(['serial_number' => 'Mã serial không hợp lệ.']);
        }
        $activation = $data['activation_date'] ?? ($current?->activation_date?->toDateString() ?? now()->toDateString());

        if ($mode === 'order') {
            $order = Order::query()->lockForUpdate()->findOrFail((int) $data['order_id']);
            $item = OrderItem::query()->where('order_id', $order->id)->findOrFail((int) $data['order_item_id']);

            return [
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'serial_number' => $serial,
                'product_name' => $item->product_name,
                'customer_name' => $order->customer_name,
                'customer_phone' => $this->phones->normalize($order->customer_phone),
                'activation_date' => $activation,
                'expiration_date' => $data['expiration_date'] ?? null,
                'status' => $current?->status ?? 'active',
                'void_reason' => $current?->void_reason,
            ];
        }

        return [
            'order_id' => null,
            'order_item_id' => null,
            'serial_number' => $serial,
            'product_name' => trim((string) ($data['product_name'] ?? '')),
            'customer_name' => trim((string) ($data['customer_name'] ?? '')),
            'customer_phone' => $this->phones->normalize($data['customer_phone'] ?? null),
            'activation_date' => $activation,
            'expiration_date' => $data['expiration_date'] ?? null,
            'status' => $current?->status ?? 'active',
            'void_reason' => $current?->void_reason,
        ];
    }

    private function assertUnique(string $serial, ?int $ignoreId = null): void
    {
        $exists = Warranty::query()->whereRaw('UPPER(serial_number) = ?', [$serial])->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists();
        if ($exists) {
            throw ValidationException::withMessages(['serial_number' => 'Mã serial này đã tồn tại.']);
        }
    }
}
