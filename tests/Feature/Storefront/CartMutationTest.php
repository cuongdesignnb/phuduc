<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartMutationTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_update_remove_and_clear_are_bounded_post_mutations(): void
    {
        $product = Product::create(['name' => 'Cart item', 'slug' => 'cart-item', 'price' => 100000, 'stock' => 3, 'status' => 'active']);

        $this->post('/gio-hang/add', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect();
        $this->assertSame(['quantity' => 2], session()->get('cart.'.$product->id));

        $this->post('/gio-hang/add', ['product_id' => $product->id, 'quantity' => 2])->assertSessionHasErrors('quantity');
        $this->assertSame(['quantity' => 2], session()->get('cart.'.$product->id));

        $this->patch('/gio-hang/update', ['product_id' => $product->id, 'quantity' => 3])->assertRedirect();
        $this->patch('/gio-hang/update', ['product_id' => $product->id, 'quantity' => 0])->assertSessionHasErrors('quantity');
        $this->assertSame(['quantity' => 3], session()->get('cart.'.$product->id));

        $this->delete('/gio-hang/remove', ['product_id' => $product->id])->assertRedirect();
        $this->delete('/gio-hang/remove', ['product_id' => $product->id])->assertRedirect();
        $this->post('/gio-hang/clear')->assertRedirect();
        $this->assertSame([], session()->get('cart', []));
    }

    public function test_add_rejects_inactive_contact_price_and_out_of_stock_products(): void
    {
        foreach ([
            ['slug' => 'inactive', 'status' => 'inactive', 'price' => 100000, 'stock' => 2],
            ['slug' => 'contact', 'status' => 'active', 'price' => 0, 'stock' => 2],
            ['slug' => 'empty', 'status' => 'active', 'price' => 100000, 'stock' => 0],
        ] as $data) {
            $product = Product::create(['name' => $data['slug'], ...$data]);
            $this->post('/gio-hang/add', ['product_id' => $product->id])->assertSessionHasErrors('product_id');
        }
    }

    public function test_cart_mutation_rate_limit_is_named_and_bounded(): void
    {
        $product = Product::create(['name' => 'Rate limited', 'slug' => 'rate-limited', 'price' => 100000, 'stock' => 99, 'status' => 'active']);

        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->post('/gio-hang/add', ['product_id' => $product->id])->assertRedirect();
        }

        $this->post('/gio-hang/add', ['product_id' => $product->id])->assertStatus(429);
    }
}
