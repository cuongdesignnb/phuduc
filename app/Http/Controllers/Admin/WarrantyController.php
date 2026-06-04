<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $warranties = Warranty::with('order:id,order_number')
            ->when($request->search, fn($q, $s) => $q->where('serial_number', 'like', "%{$s}%")
                ->orWhere('product_name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Warranty/Index', [
            'warranties' => $warranties,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        $orders = Order::select('id', 'order_number', 'customer_name')->latest()->limit(100)->get();

        return Inertia::render('Admin/Warranty/Edit', [
            'warranty' => null,
            'orders' => $orders,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'serial_number' => 'required|string|max:255|unique:warranties,serial_number',
            'product_name' => 'required|string|max:255',
            'activation_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:activation_date',
            'status' => 'nullable|in:active,expired,voided',
        ]);

        Warranty::create($data);

        return redirect()->route('admin.warranties.index')->with('success', 'Bảo hành đã được tạo.');
    }

    public function edit(Warranty $warranty)
    {
        $orders = Order::select('id', 'order_number', 'customer_name')->latest()->limit(100)->get();

        return Inertia::render('Admin/Warranty/Edit', [
            'warranty' => $warranty,
            'orders' => $orders,
        ]);
    }

    public function update(Request $request, Warranty $warranty)
    {
        $data = $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'serial_number' => 'required|string|max:255|unique:warranties,serial_number,' . $warranty->id,
            'product_name' => 'required|string|max:255',
            'activation_date' => 'nullable|date',
            'expiration_date' => 'nullable|date|after_or_equal:activation_date',
            'status' => 'nullable|in:active,expired,voided',
        ]);

        $warranty->update($data);

        return redirect()->route('admin.warranties.index')->with('success', 'Bảo hành đã được cập nhật.');
    }

    public function destroy(Warranty $warranty)
    {
        $warranty->delete();
        return redirect()->route('admin.warranties.index')->with('success', 'Bảo hành đã được xóa.');
    }
}
