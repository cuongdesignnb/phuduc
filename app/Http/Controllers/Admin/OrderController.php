<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\Admin\Operations\AdminOrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(OrderIndexRequest $request, AdminOrderService $orders): Response
    {
        return Inertia::render('Admin/Order/Index', $orders->index($request->user(), $request->validated()));
    }

    public function show(Order $order, AdminOrderService $orders): Response
    {
        return Inertia::render('Admin/Order/Show', $orders->detail(request()->user(), $order));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, AdminOrderService $orders): RedirectResponse
    {
        $result = $orders->updateStatus($order, $request->user(), $request->validated());
        $unresolved = count($result['unresolved_stock_lines'] ?? []);
        if ($unresolved > 0) {
            $warning = "Đơn hàng đã được hủy, nhưng có {$unresolved} dòng hàng không thể hoàn tồn kho tự động. Vui lòng kiểm tra tồn kho thủ công.";
            Inertia::flash('warning', $warning);

            return back()->with('warning', $warning);
        }
        $success = 'Trạng thái đơn hàng đã được cập nhật.';
        Inertia::flash('success', $success);

        return back()->with('success', $success);
    }
}
