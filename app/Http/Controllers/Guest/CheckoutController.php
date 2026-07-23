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

        return Inertia::render('Guest/Checkout', [
            ...$cart,
            'warnings' => $resolved['warnings'],
            'checkout_intent' => $intents->issue(),
            'seo' => $seo->meta(['title' => 'Thanh toán', 'robots' => 'noindex, nofollow']),
        ]);
    }

    public function store(CheckoutRequest $request, CheckoutService $checkout, CheckoutIntentService $intents): RedirectResponse
    {
        if (! hash_equals((string) $intents->current(), (string) $request->string('checkout_intent'))) {
            return back()->withErrors(['checkout_intent' => 'Phiên thanh toán không hợp lệ. Vui lòng tải lại trang.']);
        }

        $order = $checkout->checkout($request->validated(), (string) $request->string('checkout_intent'));

        return redirect()->route('checkout.success', ['token' => $order->public_token])->with('success', 'Đặt hàng thành công.');
    }

    public function success(string $token, OrderPresentationService $orders, StorefrontSeoService $seo)
    {
        $order = \App\Models\Order::query()->with('items')->where('public_token', $token)->firstOrFail();

        return Inertia::render('Guest/CheckoutSuccess', [
            'order' => $orders->success($order),
            'seo' => $seo->meta(['title' => 'Đặt hàng thành công', 'robots' => 'noindex, nofollow']),
        ]);
    }
}
