<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarrantyRequest;
use App\Http\Requests\Admin\UpdateWarrantyRequest;
use App\Http\Requests\Admin\VoidWarrantyRequest;
use App\Http\Requests\Admin\WarrantyIndexRequest;
use App\Http\Requests\Admin\WarrantyOrderLookupRequest;
use App\Models\Order;
use App\Models\Warranty;
use App\Services\Admin\Operations\AdminWarrantyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WarrantyController extends Controller
{
    public function index(WarrantyIndexRequest $request, AdminWarrantyService $warranties): Response
    {
        return Inertia::render('Admin/Warranty/Index', $warranties->index($request->user(), $request->validated()));
    }

    public function create(AdminWarrantyService $warranties): Response
    {
        return Inertia::render('Admin/Warranty/Edit', $warranties->editPage(request()->user(), null));
    }

    public function store(StoreWarrantyRequest $request, AdminWarrantyService $warranties): RedirectResponse
    {
        $warranty = $warranties->store($request->validated());

        return redirect()->route('admin.warranties.edit', $warranty)->with('success', 'Bảo hành đã được tạo.');
    }

    public function edit(Warranty $warranty, AdminWarrantyService $warranties): Response
    {
        return Inertia::render('Admin/Warranty/Edit', $warranties->editPage(request()->user(), $warranty));
    }

    public function update(UpdateWarrantyRequest $request, Warranty $warranty, AdminWarrantyService $warranties): RedirectResponse
    {
        $warranties->update($warranty, $request->validated());

        return redirect()->route('admin.warranties.edit', $warranty)->with('success', 'Bảo hành đã được cập nhật.');
    }

    public function void(VoidWarrantyRequest $request, Warranty $warranty, AdminWarrantyService $warranties): RedirectResponse
    {
        $warranties->void($warranty, $request->validated());

        return back()->with('success', 'Bảo hành đã được hủy.');
    }

    public function orders(WarrantyOrderLookupRequest $request, AdminWarrantyService $warranties): JsonResponse
    {
        $items = $warranties->orderLookup($request->validated());

        return response()->json(['items' => $items, 'data' => $items]);
    }

    public function orderItems(Order $order, AdminWarrantyService $warranties): JsonResponse
    {
        return response()->json(['data' => $warranties->orderItems($order)]);
    }
}
