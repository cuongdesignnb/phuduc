<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddToCartRequest;
use App\Http\Requests\Storefront\RemoveCartItemRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Services\Storefront\CartPresentationService;
use App\Services\Storefront\CartResolver;
use App\Services\Storefront\CartSessionService;
use App\Services\Storefront\StorefrontSeoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(CartResolver $resolver, CartPresentationService $presentation, StorefrontSeoService $seo)
    {
        $resolved = $resolver->resolve();
        $cart = $presentation->cart($resolved['entries']);

        return Inertia::render('Guest/Cart', [
            ...$cart,
            'warnings' => $resolved['warnings'],
            'seo' => $seo->meta(['title' => 'Giỏ hàng', 'robots' => 'noindex, nofollow']),
        ]);
    }

    public function add(AddToCartRequest $request, CartResolver $resolver, CartSessionService $sessionCart): RedirectResponse
    {
        $productId = (int) $request->integer('product_id');
        $quantity = (int) ($request->integer('quantity') ?: 1);
        $product = $resolver->product($productId);

        if (! $product || $product->status !== 'active' || (int) $product->price <= 0) {
            return back()->withErrors(['product_id' => 'Sản phẩm không còn khả dụng.']);
        }
        if ((int) $product->stock < 1) {
            return back()->withErrors(['product_id' => 'Sản phẩm hiện đã hết hàng.']);
        }

        $cart = $sessionCart->normalize();
        $nextQuantity = min(99, ($cart[$productId]['quantity'] ?? 0) + $quantity);
        if ($nextQuantity > (int) $product->stock) {
            return back()->withErrors(['quantity' => 'Số lượng vượt quá tồn kho hiện tại.']);
        }

        $cart[$productId] = ['quantity' => $nextQuantity];
        $sessionCart->put($cart);

        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function update(UpdateCartItemRequest $request, CartResolver $resolver, CartSessionService $sessionCart): RedirectResponse
    {
        $productId = (int) $request->integer('product_id');
        $quantity = (int) $request->integer('quantity');
        $cart = $sessionCart->normalize();

        if ($quantity === 0) {
            unset($cart[$productId]);
            $sessionCart->put($cart);

            return back()->with('success', 'Đã cập nhật giỏ hàng.');
        }

        $product = $resolver->product($productId);
        if (! $product || $product->status !== 'active' || (int) $product->price <= 0 || (int) $product->stock < 1) {
            return back()->withErrors(['quantity' => 'Sản phẩm không còn khả dụng.']);
        }
        if ($quantity > min(99, (int) $product->stock)) {
            return back()->withErrors(['quantity' => 'Số lượng vượt quá tồn kho hiện tại.']);
        }

        $cart[$productId] = ['quantity' => $quantity];
        $sessionCart->put($cart);

        return back()->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function remove(RemoveCartItemRequest $request, CartSessionService $sessionCart): RedirectResponse
    {
        $cart = $sessionCart->normalize();
        unset($cart[(int) $request->integer('product_id')]);
        $sessionCart->put($cart);

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function clear(CartSessionService $sessionCart): RedirectResponse
    {
        $sessionCart->clear();

        return back()->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }
}
