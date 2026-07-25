<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Product;
use App\Services\Storefront\NavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTargetSlugRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_menu_resolves_the_current_target_slug_each_time(): void
    {
        $product = Product::create(['name' => 'Tấm pin', 'slug' => 'tam-pin-cu', 'status' => 'active']);
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        MenuItem::create(['menu_id' => $menu->id, 'title' => 'Tấm pin', 'model_type' => 'product', 'model_id' => $product->id, 'sort_order' => 0]);

        $first = app(NavigationService::class)->get()['header'][0]['url'];
        $product->update(['slug' => 'tam-pin-moi']);
        $second = (new NavigationService(app(\App\Services\Navigation\MenuTargetResolver::class)))->get()['header'][0]['url'];

        $this->assertStringContainsString('/san-pham/tam-pin-cu', $first);
        $this->assertStringContainsString('/san-pham/tam-pin-moi', $second);
    }
}
