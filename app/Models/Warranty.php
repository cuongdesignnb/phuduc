<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    protected $fillable = [
        'order_id', 'order_item_id', 'serial_number', 'product_name',
        'customer_name', 'customer_phone', 'activation_date', 'expiration_date', 'status', 'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'activation_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
