<?php

namespace Tests\Feature\Storefront;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontQueryCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_queries_do_not_scale_with_product_count(): void
    {
        $this->createProducts(1, 'product-one');
        $one = $this->countRequestQueries(fn () => $this->get('/san-pham')->assertOk());

        $this->resetStorefrontFixtures();
        $this->createProducts(12, 'product-many');
        $many = $this->countRequestQueries(fn () => $this->get('/san-pham')->assertOk());

        $this->recordCounts([
            'PRODUCT_INDEX_1' => $one,
            'PRODUCT_INDEX_12' => $many,
        ]);

        $this->assertLessThanOrEqual($one + 1, $many);
    }

    public function test_product_detail_queries_do_not_scale_with_images_reviews_or_related_products(): void
    {
        $product = $this->createProduct('Measured detail one', 'measured-detail-one', now());
        $this->createProductImages($product, normalImages: 1, spinFrames: 1);
        $this->createReviews($product, 1);
        $this->createProducts(1, 'related-one');
        $one = $this->countRequestQueries(fn () => $this->get('/san-pham/measured-detail-one')->assertOk());

        $this->resetStorefrontFixtures();
        $product = $this->createProduct('Measured detail many', 'measured-detail-many', now());
        $this->createProductImages($product, normalImages: 6, spinFrames: 6);
        $this->createReviews($product, 20);
        $this->createProducts(4, 'related-many');
        $many = $this->countRequestQueries(fn () => $this->get('/san-pham/measured-detail-many')->assertOk());

        $this->recordCounts([
            'PRODUCT_DETAIL_ONE' => $one,
            'PRODUCT_DETAIL_MANY' => $many,
        ]);

        $this->assertLessThanOrEqual($one + 1, $many);
    }

    public function test_news_index_queries_do_not_scale_with_post_count(): void
    {
        $this->createPosts(1, null, 'post-one');
        $one = $this->countRequestQueries(fn () => $this->get('/tin-tuc')->assertOk());

        $this->resetStorefrontFixtures();
        $this->createPosts(12, null, 'post-many');
        $many = $this->countRequestQueries(fn () => $this->get('/tin-tuc')->assertOk());

        $this->recordCounts([
            'NEWS_INDEX_1' => $one,
            'NEWS_INDEX_12' => $many,
        ]);

        $this->assertLessThanOrEqual($one + 1, $many);
    }

    public function test_news_detail_queries_do_not_scale_with_related_post_count(): void
    {
        $category = PostCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        $post = $this->createPost($category, 'Measured article one', 'measured-article-one', now());
        $this->createPosts(1, $category, 'related-one');
        $one = $this->countRequestQueries(fn () => $this->get('/tin-tuc/'.$post->slug)->assertOk());

        $this->resetStorefrontFixtures();
        $category = PostCategory::create(['name' => 'Guides', 'slug' => 'guides']);
        $post = $this->createPost($category, 'Measured article many', 'measured-article-many', now());
        $this->createPosts(4, $category, 'related-many');
        $many = $this->countRequestQueries(fn () => $this->get('/tin-tuc/'.$post->slug)->assertOk());

        $this->recordCounts([
            'NEWS_DETAIL_1_RELATED' => $one,
            'NEWS_DETAIL_4_RELATED' => $many,
        ]);

        $this->assertLessThanOrEqual($one + 1, $many);
    }

    public function test_about_query_count_is_bounded(): void
    {
        Setting::set('about.title', 'Giới thiệu');
        Setting::set('about.content', '<p>Nội dung giới thiệu</p>');
        Setting::set('site.name', 'Phú Đức');

        for ($i = 1; $i <= 30; $i++) {
            Setting::set("unrelated.{$i}", "Value {$i}");
        }

        $count = $this->countRequestQueries(fn () => $this->get('/gioi-thieu')->assertOk());

        $this->recordCounts(['ABOUT' => $count]);
        $this->assertLessThanOrEqual(8, $count);
    }

    private function countRequestQueries(callable $request): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $request();

            return collect(DB::getQueryLog())
                ->reject(fn (array $query) => str_starts_with(strtolower($query['query'] ?? ''), 'pragma '))
                ->count();
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function recordCounts(array $counts): void
    {
        fwrite(STDERR, PHP_EOL);
        foreach ($counts as $key => $value) {
            fwrite(STDERR, $key.'='.$value.PHP_EOL);
        }
    }

    private function resetStorefrontFixtures(): void
    {
        Review::query()->delete();
        ProductImage::query()->delete();
        Product::query()->delete();
        Post::query()->delete();
        PostCategory::query()->delete();
        Setting::query()->delete();
    }

    private function createProducts(int $count, string $prefix = 'product'): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $product = $this->createProduct("Product {$prefix} {$i}", "{$prefix}-{$i}", now()->subMinutes($i));
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => "products/{$prefix}-{$i}.webp",
                'sort_order' => 1,
            ]);
        }
    }

    private function createProduct(string $name, string $slug, mixed $createdAt): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'price' => 1000000,
            'stock' => 5,
            'specifications' => [['key' => 'payload', 'value' => '500 kg']],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function createProductImages(Product $product, int $normalImages, int $spinFrames): void
    {
        for ($i = 1; $i <= $normalImages; $i++) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => "products/detail-normal-{$i}.webp",
                'is_360' => false,
                'sort_order' => $i,
            ]);
        }

        for ($i = 1; $i <= $spinFrames; $i++) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => "products/detail-spin-{$i}.webp",
                'is_360' => true,
                'sort_order' => $normalImages + $i,
            ]);
        }
    }

    private function createReviews(Product $product, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Review::create([
                'product_id' => $product->id,
                'customer_name' => "Reviewer {$i}",
                'content' => "Review {$i}",
                'rating' => 4,
                'status' => 'approved',
            ]);
        }
    }

    private function createPosts(int $count, ?PostCategory $category = null, string $prefix = 'post'): void
    {
        $category ??= PostCategory::create(['name' => 'News', 'slug' => 'news']);

        for ($i = 1; $i <= $count; $i++) {
            $this->createPost($category, "Post {$prefix} {$i}", "{$prefix}-{$i}", now()->subMinutes($i));
        }
    }

    private function createPost(PostCategory $category, string $title, string $slug, mixed $createdAt): Post
    {
        return Post::create([
            'post_category_id' => $category->id,
            'title' => $title,
            'slug' => $slug,
            'summary' => 'Summary',
            'content' => '<p>Body</p>',
            'featured_image' => "posts/{$slug}.webp",
            'status' => 'published',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
