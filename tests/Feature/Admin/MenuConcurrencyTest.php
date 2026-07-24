<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Services\Admin\Content\AdminMenuService;
use App\Services\Admin\Content\MenuItemSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MenuConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_menu_version_is_rejected(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $this->expectException(ValidationException::class);
        app(MenuItemSyncService::class)->sync($menu, [], '2000-01-01T00:00:00.000000Z');
    }

    public function test_stale_menu_details_version_is_rejected(): void
    {
        $menu = Menu::create(['name' => 'Header', 'location' => 'header']);
        $this->expectException(ValidationException::class);
        app(AdminMenuService::class)->update($menu, ['name' => 'Changed', 'location' => 'header', 'version' => '2000-01-01T00:00:00.000000Z']);
    }
}
