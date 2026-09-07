<?php

namespace Tests\Feature\Seeders;

use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Database\Seeders\PhuDucSeoProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PhuDucSeoProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_all_catalogue_products_with_seo_content_and_variants(): void
    {
        Storage::fake('public');

        $this->seed(PhuDucSeoProductSeeder::class);

        $this->assertSame(28, Product::query()->count());
        $this->assertSame(48, ProductVariant::query()->count());

        $crane = Product::query()->where('slug', 'cau-dien-thuy-luc-2-tan')->firstOrFail();
        $this->assertSame('Cẩu điện thủy lực 2 tấn', $crane->name);
        $this->assertSame('2 tấn', $crane->specifications[1]['value']);
        $this->assertStringContainsString('Thiết bị liên quan', $crane->description);
        $this->assertStringContainsString('/san-pham/cau-thuy-luc-500-kg', $crane->description);
        $this->assertNotEmpty($crane->meta_title);
        $this->assertNotEmpty($crane->meta_description);
    }

    public function test_it_preserves_existing_gallery_and_adds_an_alt_text_to_each_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/existing/hero.webp', 'image content');

        $product = Product::create([
            'name' => 'Cẩu điện thủy lực 2 tấn cũ',
            'slug' => 'cau-dien-thuy-luc-2-tan',
            'status' => 'active',
        ]);
        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/existing/hero.webp',
            'sort_order' => 0,
        ]);

        $this->seed(PhuDucSeoProductSeeder::class);

        $this->assertSame(1, ProductImage::query()->count());
        $this->assertSame('Cẩu điện thủy lực 2 tấn – ảnh sản phẩm 1', $image->fresh()->alt_text);
        $this->assertDatabaseHas('media_libraries', [
            'file_path' => 'products/existing/hero.webp',
            'alt_text' => 'Cẩu điện thủy lực 2 tấn – ảnh sản phẩm 1',
        ]);
        $this->assertSame(1, MediaLibrary::query()->count());

        $this->get('/san-pham/cau-dien-thuy-luc-2-tan')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Guest/Product/Show')
                ->where('page.product.gallery.0.alt', 'Cẩu điện thủy lực 2 tấn – ảnh sản phẩm 1')
                ->where('page.product.specifications.1.value', '2 tấn'));
    }

    public function test_it_is_idempotent_and_never_duplicates_the_catalogue_or_variants(): void
    {
        $this->seed(PhuDucSeoProductSeeder::class);
        $this->seed(PhuDucSeoProductSeeder::class);

        $this->assertSame(28, Product::query()->count());
        $this->assertSame(48, ProductVariant::query()->count());
    }
}
