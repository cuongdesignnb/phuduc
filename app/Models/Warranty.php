<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    protected $fillable = [
        'order_id', 'serial_number', 'product_name',
        'activation_date', 'expiration_date', 'status',
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
}
