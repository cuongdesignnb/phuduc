<?php

namespace Tests\Feature\Admin;

use App\Models\MediaLibrary;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRouteQueryScalingTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_admin_routes_do_not_add_queries_for_thirty_records(): void
    {
        $admin = User::factory()->admin()->create();
        $productOne = Product::create(['name' => 'Product 1', 'slug' => 'product-1', 'status' => 'active']);
        $productQ1 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.products.index')));
        foreach (range(2, 30) as $number) {
            Product::create(['name' => "Product {$number}", 'slug' => "product-{$number}", 'status' => 'active']);
        }
        $productQ30 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.products.index')));

        $mediaOne = MediaLibrary::create(['file_name' => 'image-1.webp', 'file_path' => 'media/image-1.webp', 'mime_type' => 'image/webp', 'size' => 1]);
        $mediaQ1 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.media.index')));
        foreach (range(2, 30) as $number) {
            MediaLibrary::create(['file_name' => "image-{$number}.webp", 'file_path' => "media/image-{$number}.webp", 'mime_type' => 'image/webp', 'size' => 1]);
        }
        $mediaQ30 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.media.index')));
        $pickerQ1 = $this->countRoute(fn () => $this->actingAs($admin)->getJson(route('admin.media.data', ['ids' => [$mediaOne->id]])));
        $pickerQ30 = $this->countRoute(fn () => $this->actingAs($admin)->getJson(route('admin.media.data')));

        $postQ1 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.posts.index')));
        foreach (range(2, 30) as $number) {
            Post::create(['title' => "Post {$number}", 'slug' => "post-{$number}", 'status' => 'draft']);
        }
        $postQ30 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.posts.index')));

        $categoryQ1 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.post-categories.index')));
        foreach (range(2, 30) as $number) {
            PostCategory::create(['name' => "Category {$number}", 'slug' => "category-{$number}"]);
        }
        $categoryQ30 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.post-categories.index')));

        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Item 1', 'model_type' => 'url', 'url' => '/', 'sort_order' => 0]);
        $menuQ1 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.menus.edit', $menu)));
        foreach (range(2, 30) as $number) {
            MenuItem::create(['menu_id' => $menu->id, 'title' => "Item {$number}", 'model_type' => 'url', 'url' => '/', 'sort_order' => $number]);
        }
        $menuQ30 = $this->countRoute(fn () => $this->actingAs($admin)->get(route('admin.menus.edit', $menu)));

        fwrite(STDOUT, "PRODUCT_INDEX_Q1={$productQ1}\nPRODUCT_INDEX_Q30={$productQ30}\nMEDIA_INDEX_Q1={$mediaQ1}\nMEDIA_INDEX_Q30={$mediaQ30}\nPOST_INDEX_Q1={$postQ1}\nPOST_INDEX_Q30={$postQ30}\nCATEGORY_INDEX_Q1={$categoryQ1}\nCATEGORY_INDEX_Q30={$categoryQ30}\nMENU_EDIT_Q1={$menuQ1}\nMENU_EDIT_Q30={$menuQ30}\nPICKER_Q1={$pickerQ1}\nPICKER_Q30={$pickerQ30}\n");
        $this->assertLessThanOrEqual($productQ1 + 2, $productQ30);
        $this->assertLessThanOrEqual($mediaQ1 + 2, $mediaQ30);
        $this->assertLessThanOrEqual($postQ1 + 2, $postQ30);
        $this->assertLessThanOrEqual($categoryQ1 + 2, $categoryQ30);
        $this->assertLessThanOrEqual($menuQ1 + 2, $menuQ30);
        $this->assertLessThanOrEqual($pickerQ1 + 2, $pickerQ30);
    }

    private function countRoute(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();

        return count(DB::getQueryLog());
    }
}
