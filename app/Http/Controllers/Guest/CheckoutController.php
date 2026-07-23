<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Services\Storefront\CartPresentationService;
use App\Services\Storefront\CartResolver;
use App\Services\Storefront\CheckoutIntentService;
use App\Services\Storefront\CheckoutService;
use App\Services\Storefront\OrderPresentationService;
use App\Services\Storefront\StorefrontSeoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index(CartResolver $resolver, CartPresentationService $presentation, CheckoutIntentService $intents, StorefrontSeoService $seo)
    {
        $resolved = $resolver->resolve();
        if ($resolved['entries'] === []) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống.');
        }

        $cart = $presentation->cart($resolved['entries']);
        $meta = $seo->meta(['title' => 'Thanh toán', 'robots' => 'noindex, nofollow']);

        return Inertia::render('Guest/Checkout', [
            'page' => [
                'type' => 'checkout',
                'seo' => $meta,
                'breadcrumbs' => [
                    ['name' => 'Trang chủ', 'url' => route('home')],
                    ['name' => 'Giỏ hàng', 'url' => route('cart.index')],
                    ['name' => 'Thanh toán'],
                ],
                'checkout' => [
                    'intent' => $intents->issue(),
                    'cart' => [...$cart, 'warnings' => $resolved['warnings']],
                    'fields' => ['customer_name', 'customer_phone', 'customer_email', 'shipping_address', 'notes'],
                ],
            ],
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout): RedirectResponse
    {
        $order = $checkout->checkout($request->validated(), (string) $request->string('checkout_intent'));

        return redirect()->route('checkout.success', ['token' => $order->public_token])->with('success', 'Đặt hàng thành công.');
    }

    public function success(string $token, OrderPresentationService $orders, StorefrontSeoService $seo)
    {
        $order = \App\Models\Order::query()->with('items')->where('public_token', $token)->firstOrFail();

        return Inertia::render('Guest/CheckoutSuccess', [
            'page' => [
                'type' => 'checkout_success',
                'seo' => $seo->meta(['title' => 'Đặt hàng thành công', 'robots' => 'noindex, nofollow']),
                'breadcrumbs' => [
                    ['name' => 'Trang chủ', 'url' => route('home')],
                    ['name' => 'Đặt hàng thành công'],
                ],
                'order' => $orders->success($order),
            ],
        ]);
    }
}
