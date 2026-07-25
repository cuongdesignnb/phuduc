<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAdminMutationTest extends TestCase
{
    use RefreshDatabase, Pr3bTestHelpers;

    public function test_product_creation_normalizes_slug_price_and_specifications(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.products.store'), ['name' => 'Solar Panel', 'slug' => '', 'price' => '120000', 'stock' => '2', 'status' => 'active', 'specifications' => [['key' => ' Watt ', 'value' => ' 500 ']]]);
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['slug' => 'solar-panel', 'price' => 120000]);
        $this->assertSame('Watt', Product::firstOrFail()->specifications[0]['key']);
    }
}
