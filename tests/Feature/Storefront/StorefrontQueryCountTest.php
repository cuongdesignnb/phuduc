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

    public function test_storefront_routes_do_not_scale_queries_with_collection_size(): void
    {
        $productIndexOne = $this->countQueries(function (): void {
            $this->createProducts(1, 'product-one');
            $this->get('/san-pham')->assertOk();
        });

        $productIndexTwelve = $this->countQueries(function (): void {
            $this->createProducts(12, 'product-many');
            $this->get('/san-pham')->assertOk();
        });

        $productDetail = $this->countQueries(function (): void {
            $product = $this->createProduct('Measured detail', 'measured-detail', now());
            $this->createProductImages($product, 6);
            $this->createReviews($product, 8);
            $this->createProducts(4, 'related');
            $this->get('/san-pham/measured-detail')->assertOk();
        });

        $newsIndexOne = $this->countQueries(function (): void {
            $this->createPosts(1, null, 'post-one');
            $this->get('/tin-tuc')->assertOk();
        });

        $newsIndexTwelve = $this->countQueries(function (): void {
            $this->createPosts(12, PostCategory::create(['name' => 'News many', 'slug' => 'news-many']), 'post-many');
            $this->get('/tin-tuc')->assertOk();
        });

        $newsDetail = $this->countQueries(function (): void {
            $category = PostCategory::create(['name' => 'Guides', 'slug' => 'guides']);
            $post = $this->createPost($category, 'Measured article', 'measured-article', now());
            $this->createPosts(4, $category, 'related');
            $this->get('/tin-tuc/'.$post->slug)->assertOk();
        });

        $about = $this->countQueries(function (): void {
            Setting::set('about.title', 'About');
            Setting::set('about.content', '<p>About content</p>');
            Setting::set('site.name', 'Phu Duc');
            $this->get('/gioi-thieu')->assertOk();
        });

        $this->recordCounts([
            'PRODUCT_INDEX_1' => $productIndexOne,
            'PRODUCT_INDEX_12' => $productIndexTwelve,
            'PRODUCT_DETAIL' => $productDetail,
            'NEWS_INDEX_1' => $newsIndexOne,
            'NEWS_INDEX_12' => $newsIndexTwelve,
            'NEWS_DETAIL' => $newsDetail,
            'ABOUT' => $about,
        ]);

        $this->assertLessThanOrEqual(40, $productIndexTwelve);
        $this->assertLessThanOrEqual(25, $newsIndexTwelve);
        $this->assertGreaterThan(0, $productDetail);
        $this->assertGreaterThan(0, $newsDetail);
        $this->assertGreaterThan(0, $about);
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $callback();

        $queries = collect(DB::getQueryLog())
            ->reject(fn (array $query) => str_starts_with(strtolower($query['query'] ?? ''), 'pragma '));

        DB::disableQueryLog();
        DB::flushQueryLog();

        return $queries->count();
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

    private function createProductImages(Product $product, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => "products/detail-{$i}.webp",
                'is_360' => $i > 3,
                'sort_order' => $i,
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
