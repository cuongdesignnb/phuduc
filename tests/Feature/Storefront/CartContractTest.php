<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CartContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_uses_canonical_page_and_numeric_money_contract(): void
    {
        $product = $this->product(['name' => 'Database name', 'price' => 1250000, 'stock' => 4]);
        ProductImage::create(['product_id' => $product->id, 'image_path' => 'products/current.webp']);

        $this->withSession(['cart' => [$product->id => ['quantity' => 2, 'name' => 'Tampered', 'price' => 1]]])
            ->get('/gio-hang')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Cart')
                ->where('page.type', 'cart')
                ->where('page.cart.items.0.name', 'Database name')
                ->where('page.cart.items.0.unit_price', 1250000)
                ->where('page.cart.items.0.unit_price_display', '1.250.000 ₫')
                ->where('page.cart.items.0.subtotal', 2500000)
                ->where('page.cart.items.0.subtotal_display', '2.500.000 ₫')
                ->where('page.cart.items.0.max_quantity', 4)
                ->where('page.cart.summary.item_count', 1)
                ->where('page.cart.summary.quantity_count', 2)
                ->where('page.cart.summary.subtotal', 2500000)
                ->where('page.cart.summary.total', 2500000)
                ->where('page.cart.can_checkout', true)
                ->where('page.seo.robots', 'noindex, nofollow')
                ->where('page.breadcrumbs.1.name', 'Giỏ hàng')
            );

        $this->assertSame(['quantity' => 2], session()->get('cart.'.$product->id));
    }

    public function test_cart_removes_stale_and_unavailable_products(): void
    {
        $active = $this->product(['stock' => 1]);
        $inactive = $this->product(['slug' => 'inactive', 'status' => 'inactive']);

        $this->withSession(['cart' => [$active->id => ['quantity' => 3], $inactive->id => ['quantity' => 1], 99999 => ['quantity' => 1]]])
            ->get('/gio-hang')
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.cart.items.0.quantity', 1)
                ->count('page.cart.items', 1)
                ->count('page.cart.warnings', 3)
            );
    }

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test product',
            'slug' => 'product-'.uniqid(),
            'price' => 1000000,
            'stock' => 5,
            'status' => 'active',
        ], $overrides));
    }
}
