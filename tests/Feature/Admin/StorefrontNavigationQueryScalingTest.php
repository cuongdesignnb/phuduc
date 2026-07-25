<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Services\Navigation\MenuTargetResolver;
use App\Services\Storefront\NavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontNavigationQueryScalingTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_url_navigation_is_bounded(): void
    {
        [$q1, $q30] = $this->measureCustomUrls();
        fwrite(STDOUT, "NAVIGATION_CUSTOM_Q1={$q1}\nNAVIGATION_CUSTOM_Q30={$q30}\n");
        $this->assertLessThanOrEqual($q1 + 3, $q30);
    }

    public function test_product_target_navigation_is_bounded(): void
    {
        [$q1, $q30] = $this->measureTargets('product');
        fwrite(STDOUT, "NAVIGATION_PRODUCT_Q1={$q1}\nNAVIGATION_PRODUCT_Q30={$q30}\n");
        $this->assertLessThanOrEqual($q1 + 3, $q30);
    }

    public function test_post_target_navigation_is_bounded(): void
    {
        [$q1, $q30] = $this->measureTargets('post');
        fwrite(STDOUT, "NAVIGATION_POST_Q1={$q1}\nNAVIGATION_POST_Q30={$q30}\n");
        $this->assertLessThanOrEqual($q1 + 3, $q30);
    }

    public function test_category_target_navigation_is_bounded(): void
    {
        [$q1, $q30] = $this->measureTargets('category');
        fwrite(STDOUT, "NAVIGATION_CATEGORY_Q1={$q1}\nNAVIGATION_CATEGORY_Q30={$q30}\n");
        $this->assertLessThanOrEqual($q1 + 3, $q30);
    }

    public function test_mixed_targets_and_four_level_tree_are_bounded(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $product = Product::create(['name' => 'Tấm pin', 'slug' => 'tam-pin', 'status' => 'active']);
        $post = Post::create(['title' => 'Bài viết', 'slug' => 'bai-viet', 'status' => 'published']);
        $category = PostCategory::create(['name' => 'Tin tức', 'slug' => 'tin-tuc']);
        $parent = null;
        foreach (range(0, 3) as $level) {
            $parent = MenuItem::create(['menu_id' => $menu->id, 'parent_id' => $parent?->id, 'title' => "Mục {$level}", 'model_type' => ['product', 'post', 'category', 'url'][$level], 'model_id' => [$product->id, $post->id, $category->id, null][$level], 'url' => $level === 3 ? '/' : null, 'sort_order' => 0]);
        }

        $queries = $this->measure(fn () => (new NavigationService(app(MenuTargetResolver::class)))->get());
        fwrite(STDOUT, "NAVIGATION_MIXED_Q30={$queries}\n");
        $this->assertLessThanOrEqual(8, $queries);
        $this->assertNotEmpty($parent);
    }

    private function measureCustomUrls(): array
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Một', 'model_type' => 'url', 'url' => '/', 'sort_order' => 0]);
        $q1 = $this->measureNavigation();
        foreach (range(2, 30) as $i) {
            MenuItem::create(['menu_id' => $menu->id, 'title' => "Mục {$i}", 'model_type' => 'url', 'url' => "/muc-{$i}", 'sort_order' => $i]);
        }
        $q30 = $this->measureNavigation();

        return [$q1, $q30];
    }

    private function measureTargets(string $type): array
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $this->createTarget($menu, $type, 1);
        $q1 = $this->measureNavigation();
        foreach (range(2, 30) as $i) {
            $this->createTarget($menu, $type, $i);
        }
        $q30 = $this->measureNavigation();

        return [$q1, $q30];
    }

    private function createTarget(Menu $menu, string $type, int $number): void
    {
        $modelId = match ($type) {
            'product' => Product::create(['name' => "Tấm pin {$number}", 'slug' => "tam-pin-{$number}", 'status' => 'active'])->id,
            'post' => Post::create(['title' => "Bài viết {$number}", 'slug' => "bai-viet-{$number}", 'status' => 'published'])->id,
            'category' => PostCategory::create(['name' => "Tin {$number}", 'slug' => "tin-{$number}"])->id,
        };
        MenuItem::create(['menu_id' => $menu->id, 'title' => "Mục {$number}", 'model_type' => $type, 'model_id' => $modelId, 'sort_order' => $number]);
    }

    private function measureNavigation(): int
    {
        return $this->measure(fn () => (new NavigationService(app(MenuTargetResolver::class)))->get());
    }

    private function measure(callable $callback): int
    {
        $count = 0;
        DB::listen(function () use (&$count): void {
            $count++;
        });
        $callback();

        return $count;
    }
}
