<?php

namespace Tests\Feature\Seeders;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductImage;
use Database\Seeders\PhuDucSeoProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PhuDucSeoProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_approved_seo_product_catalogue_with_media_and_alt_text(): void
    {
        Storage::fake('public');

        $this->seed(PhuDucSeoProductSeeder::class);

        $products = Product::query()
            ->whereIn('sku', [
                'PD-CRANE-HYD-1000',
                'PD-SPIDER-5T',
                'PD-GANTRY-MOBILE',
                'PD-LOADER-904',
                'PD-FORKLIFT-ELECTRIC-4W',
            ])
            ->with('images')
            ->get();

        $this->assertCount(5, $products);
        $this->assertSame(15, ProductImage::query()->count());
        $this->assertSame(15, MediaLibrary::query()->count());

        foreach ($products as $product) {
            $this->assertSame('active', $product->status);
            $this->assertSame(0, (int) $product->price);
            $this->assertNotEmpty($product->meta_title);
            $this->assertNotEmpty($product->meta_description);
            $this->assertStringContainsString('Thiết bị liên quan', $product->description);
            $this->assertCount(3, $product->images);

            foreach ($product->images as $image) {
                $this->assertNotEmpty($image->alt_text);
                Storage::disk('public')->assertExists($image->image_path);
            }
        }

        $this->get('/san-pham/cau-dien-thuy-luc-1-tan-xoay-360-do')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Product/Show')
                ->where('page.product.gallery.0.alt', 'Cẩu điện thủy lực 1 tấn xoay 360 độ lắp trên xe tải nhẹ')
                ->where('page.product.specifications.0.value', '1.000 kg tại tầm với phù hợp; tải giảm theo độ vươn cần'));
    }

    public function test_it_is_idempotent_and_keeps_the_catalogue_at_five_products(): void
    {
        Storage::fake('public');

        $this->seed(PhuDucSeoProductSeeder::class);
        $this->seed(PhuDucSeoProductSeeder::class);

        $this->assertSame(5, Product::query()->whereIn('sku', [
            'PD-CRANE-HYD-1000',
            'PD-SPIDER-5T',
            'PD-GANTRY-MOBILE',
            'PD-LOADER-904',
            'PD-FORKLIFT-ELECTRIC-4W',
        ])->count());
        $this->assertSame(15, ProductImage::query()->count());
        $this->assertSame(15, MediaLibrary::query()->count());
    }
}
