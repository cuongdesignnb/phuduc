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

    public function test_cart_normalizes_session_and_uses_current_database_presentation(): void
    {
        $product = $this->product(['name' => 'Tên từ cơ sở dữ liệu', 'price' => 1250000, 'stock' => 4]);
        ProductImage::create(['product_id' => $product->id, 'image_path' => 'products/current.webp']);

        $this->withSession(['cart' => [$product->id => ['quantity' => 2, 'name' => 'Tên giả', 'price' => 1, 'image' => 'old.webp']]])
            ->get('/gio-hang')
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Cart')
                ->where('items.0.name', 'Tên từ cơ sở dữ liệu')
                ->where('items.0.unit_price_display', '1.250.000 ₫')
                ->where('items.0.line_total_display', '2.500.000 ₫')
                ->where('summary.total_display', '2.500.000 ₫')
                ->where('seo.robots', 'noindex, nofollow')
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
                ->where('items.0.quantity', 1)
                ->count('items', 1)
                ->count('warnings', 3)
            );
    }

    private function product(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Sản phẩm thử',
            'slug' => 'product-'.uniqid(),
            'price' => 1000000,
            'stock' => 5,
            'status' => 'active',
        ], $overrides));
    }
}
