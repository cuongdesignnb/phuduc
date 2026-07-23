<?php

namespace Tests\Feature\Storefront;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VietnameseContentContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_contract_uses_accented_vietnamese_labels(): void
    {
        $product = Product::create([
            'name' => 'Xe nâng điện',
            'slug' => 'xe-nang-dien',
            'price' => 1000000,
            'status' => 'active',
            'specifications' => [
                ['key' => 'payload', 'value' => '500 kg'],
                ['key' => 'quãng đường', 'value' => '80 km'],
            ],
        ]);
        ProductImage::create(['product_id' => $product->id, 'image_path' => 'products/card.webp']);

        $this->get('/san-pham?sort=price_asc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.breadcrumbs.0.name', 'Trang chủ')
                ->where('page.breadcrumbs.1.name', 'Sản phẩm')
                ->where('page.hero.title', 'Giải pháp xe điện công nghiệp')
                ->where('page.catalog.sort_options.1.label', 'Giá tăng dần')
                ->where('page.catalog.items.0.price_display', '1.000.000 ₫')
                ->where('page.catalog.items.0.card_specifications.0.label', 'Tải trọng')
                ->where('page.catalog.items.0.card_specifications.1.label', 'Quãng đường')
            );
    }

    public function test_product_detail_contract_uses_accented_contact_price(): void
    {
        Product::create([
            'name' => 'Xe cần báo giá',
            'slug' => 'xe-can-bao-gia',
            'price' => 0,
            'status' => 'active',
            'specifications' => [['key' => 'tải trọng nâng', 'value' => '1 tấn']],
        ]);

        $this->get('/san-pham/xe-can-bao-gia')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.product.price_display', 'Liên hệ')
            );
    }

    public function test_news_and_about_contracts_use_accented_vietnamese_labels(): void
    {
        $category = PostCategory::create(['name' => 'Tin tức', 'slug' => 'tin-tuc']);
        Post::create(['post_category_id' => $category->id, 'title' => 'Bài viết', 'slug' => 'bai-viet', 'summary' => 'Tóm tắt', 'status' => 'published']);

        $this->get('/tin-tuc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.breadcrumbs.0.name', 'Trang chủ')
                ->where('page.breadcrumbs.1.name', 'Tin tức')
                ->where('page.hero.title', 'Tin tức mới nhất')
            );

        $this->get('/gioi-thieu')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('page.breadcrumbs.0.name', 'Trang chủ')
                ->where('page.breadcrumbs.1.name', 'Giới thiệu')
            );
    }
}
