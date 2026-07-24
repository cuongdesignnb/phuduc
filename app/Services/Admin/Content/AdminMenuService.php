<?php

namespace App\Services\Admin\Content;

use App\Models\Menu;
use App\Models\User;
use App\Services\Admin\AdminPageService;
use Illuminate\Support\Facades\DB;

class AdminMenuService
{
    public function __construct(private readonly AdminPageService $pages, private readonly AdminMenuPresentationService $presentation, private readonly MenuItemSyncService $sync) {}
    public function index(User $user): array { $menus = Menu::query()->withCount('allItems')->latest()->get(); return $this->pages->envelope($user, 'admin_menus_index', 'Menu', [['label' => 'Menu', 'url' => route('admin.menus.index')]], ['items' => $menus->map(fn (Menu $menu) => $this->presentation->item($menu))->all(), 'locations' => MenuLocationRegistry::all()]); }
    public function editPage(User $user, ?Menu $menu): array { $label = $menu ? 'Sửa menu' : 'Thêm menu'; return $this->pages->envelope($user, 'admin_menus_edit', $label, [['label' => 'Menu', 'url' => route('admin.menus.index')], ['label' => $label, 'url' => $menu ? route('admin.menus.edit', $menu) : null]], ['menu' => $menu ? $this->presentation->edit($menu->load(['items.allChildren'])) : null, 'locations' => MenuLocationRegistry::all(), 'targets' => MenuTargetRegistry::all()]); }
    public function store(array $data): Menu { return Menu::create($data); }
    public function update(Menu $menu, array $data): Menu { $menu->update($data); return $menu->refresh(); }
    public function destroy(Menu $menu): void { DB::transaction(fn () => $menu->delete()); }
    public function saveItems(Menu $menu, array $items, ?string $version): void { $this->sync->sync($menu, $items, $version); }
}
