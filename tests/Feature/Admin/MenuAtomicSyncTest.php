<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\Content\MenuItemSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAtomicSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_sync_preserves_existing_item_and_writes_nested_tree(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $version = app(AdminConcurrencyService::class)->version($menu);
        app(MenuItemSyncService::class)->sync($menu, [['title' => 'Home', 'model_type' => 'url', 'url' => '/', 'children' => [['title' => 'About', 'model_type' => 'url', 'url' => '/about', 'children' => []]]]], $version);
        $this->assertDatabaseCount('menu_items', 2);
        $this->assertDatabaseHas('menu_items', ['title' => 'About']);
    }
}
