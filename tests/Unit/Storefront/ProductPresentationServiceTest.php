<?php

namespace Tests\Unit\Storefront;

use App\Models\Product;
use App\Services\Storefront\ProductPresentationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPresentationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_price_display_and_specification_aliases_use_accented_vietnamese(): void
    {
        $product = Product::create([
            'name' => 'Xe nâng',
            'slug' => 'xe-nang',
            'price' => 1000000,
            'status' => 'active',
            'specifications' => [
                ['key' => 'tải trọng nâng', 'value' => '1 tấn'],
                ['key' => 'phạm vi hoạt động', 'value' => '80 km'],
            ],
        ]);

        $presented = app(ProductPresentationService::class)->present($product);

        $this->assertSame('1.000.000 ₫', $presented['price_display']);
        $this->assertSame('Tải trọng', $presented['card_specifications'][0]['label']);
        $this->assertSame('Quãng đường', $presented['card_specifications'][1]['label']);
    }

    public function test_empty_price_display_uses_accented_contact_label(): void
    {
        $product = Product::create([
            'name' => 'Xe báo giá',
            'slug' => 'xe-bao-gia',
            'price' => 0,
            'status' => 'active',
        ]);

        $presented = app(ProductPresentationService::class)->present($product);

        $this->assertSame('Liên hệ', $presented['price_display']);
    }
}
