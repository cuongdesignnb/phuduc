<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\Content\MenuItemSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuRepeatedSaveIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_save_reuses_the_existing_id_instead_of_recreating_the_node(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $version = app(AdminConcurrencyService::class)->version($menu);
        app(MenuItemSyncService::class)->sync($menu, [['client_key' => 'stable', 'title' => 'Trang chủ', 'model_type' => 'url', 'url' => '/', 'children' => []]], $version);
        $item = MenuItem::query()->firstOrFail();
        $version = app(AdminConcurrencyService::class)->version($menu->refresh());

        app(MenuItemSyncService::class)->sync($menu, [['id' => $item->id, 'client_key' => 'stable', 'title' => 'Trang chủ mới', 'model_type' => 'url', 'url' => '/', 'children' => []]], $version);

        $this->assertDatabaseCount('menu_items', 1);
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'title' => 'Trang chủ mới']);
    }
}
