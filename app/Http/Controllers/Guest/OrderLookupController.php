<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\OrderLookupRequest;
use App\Models\Order;
use App\Services\Storefront\OrderPresentationService;
use App\Services\Storefront\StorefrontSeoService;
use Inertia\Inertia;

class OrderLookupController extends Controller
{
    private const FAILURE = 'Không tìm thấy đơn hàng phù hợp với thông tin đã cung cấp.';

    public function index(StorefrontSeoService $seo)
    {
        return Inertia::render('Guest/OrderLookup', [
            'page' => [
                'type' => 'order_lookup',
                'seo' => $seo->meta(['title' => 'Tra cứu đơn hàng', 'robots' => 'noindex, nofollow']),
                'breadcrumbs' => [
                    ['name' => 'Trang chủ', 'url' => route('home')],
                    ['name' => 'Tra cứu đơn hàng'],
                ],
                'lookup' => ['searched' => false, 'result' => null, 'message' => null],
            ],
        ]);
    }

    public function lookup(OrderLookupRequest $request, OrderPresentationService $orders, StorefrontSeoService $seo)
    {
        $order = Order::query()
            ->with('items')
            ->where('order_number', $request->string('order_number'))
            ->where('customer_phone', $request->string('customer_phone'))
            ->first();

        return Inertia::render('Guest/OrderLookup', [
            'page' => [
                'type' => 'order_lookup',
                'seo' => $seo->meta(['title' => 'Tra cứu đơn hàng', 'robots' => 'noindex, nofollow']),
                'breadcrumbs' => [
                    ['name' => 'Trang chủ', 'url' => route('home')],
                    ['name' => 'Tra cứu đơn hàng'],
                ],
                'lookup' => [
                    'searched' => true,
                    'result' => $order ? $orders->lookup($order) : null,
                    'message' => $order ? null : self::FAILURE,
                ],
            ],
        ]);
    }
}
