<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_name', 'customer_phone', 'customer_email',
        'checkout_intent', 'public_token', 'shipping_address', 'total_amount', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->oldest('created_at')->oldest('id');
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-'.strtoupper(uniqid());
    }
}
