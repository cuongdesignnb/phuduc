<?php

namespace Tests\Feature\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Admin\Content\MenuItemSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MenuOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_from_another_menu_is_rejected(): void
    {
        $first = Menu::create(['name' => 'First', 'location' => 'header']);
        $second = Menu::create(['name' => 'Second', 'location' => 'footer']);
        $item = MenuItem::create(['menu_id' => $first->id, 'title' => 'Owned', 'model_type' => 'url', 'url' => '/', 'sort_order' => 0]);
        $this->expectException(ValidationException::class);
        app(MenuItemSyncService::class)->sync($second, [['id' => $item->id, 'title' => 'Hijack', 'model_type' => 'url', 'url' => '/', 'children' => []]], null);
    }
}
