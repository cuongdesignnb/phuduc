<?php

namespace App\Services\Admin\Content;

use App\Models\Menu;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPageService;
use Illuminate\Support\Facades\DB;

class AdminMenuService
{
    public function __construct(private readonly AdminPageService $pages, private readonly AdminMenuPresentationService $presentation, private readonly MenuItemSyncService $sync, private readonly AdminConcurrencyService $concurrency) {}

    public function index(User $user): array
    {
        $menus = Menu::query()->withCount('allItems')->latest()->get();

        return $this->pages->envelope($user, 'admin_menus_index', 'Menu', [['label' => 'Menu', 'url' => route('admin.menus.index')]], ['items' => $menus->map(fn (Menu $menu) => $this->presentation->item($menu))->all(), 'locations' => MenuLocationRegistry::all()]);
    }

    public function editPage(User $user, ?Menu $menu): array
    {
        $label = $menu ? 'Sửa menu' : 'Thêm menu';

        return $this->pages->envelope($user, 'admin_menus_edit', $label, [['label' => 'Menu', 'url' => route('admin.menus.index')], ['label' => $label, 'url' => $menu ? route('admin.menus.edit', $menu) : null]], ['menu' => $menu ? $this->presentation->edit($menu->load(['items.allChildren'])) : null, 'locations' => MenuLocationRegistry::all(), 'targets' => MenuTargetRegistry::all()]);
    }

    public function store(array $data): Menu
    {
        return Menu::create($data);
    }

    public function update(Menu $menu, array $data): Menu
    {
        return DB::transaction(function () use ($menu, $data): Menu {
            $locked = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $this->concurrency->assertVersion($data['version'] ?? null, $locked, 'Menu đã được cập nhật ở phiên khác.');
            unset($data['version']);
            $locked->update($data);

            return $locked->refresh();
        });
    }

    public function destroy(Menu $menu): void
    {
        DB::transaction(fn () => $menu->delete());
    }

    public function saveItems(Menu $menu, array $items, ?string $version): string
    {
        return $this->sync->sync($menu, $items, $version);
    }
}
