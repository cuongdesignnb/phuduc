<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderLookupController extends Controller
{
    public function index()
    {
        return Inertia::render('Guest/OrderLookup');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'customer_phone' => 'required|string',
        ]);

        $order = Order::with('items')
            ->where('order_number', $request->order_number)
            ->where('customer_phone', $request->customer_phone)
            ->first();

        return Inertia::render('Guest/OrderLookup', [
            'order' => $order,
            'searched' => true,
        ]);
    }
}
