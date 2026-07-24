<?php

namespace App\Services\Admin\Content;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Admin\AdminConcurrencyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuItemSyncService
{
    public function __construct(private readonly AdminConcurrencyService $concurrency, private readonly AdminUrlService $urls) {}

    public function sync(Menu $menu, array $items, ?string $version): string
    {
        return DB::transaction(function () use ($menu, $items, $version): string {
            $locked = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $this->concurrency->assertVersion($version, $locked, 'Menu was updated in another session. Reload and try again.');
            $existing = $locked->allItems()->get()->keyBy('id');
            $kept = [];
            $this->write($locked, $items, null, $existing, $kept);
            $deleteIds = $existing->keys()->diff($kept)->values();
            if ($deleteIds->isNotEmpty()) {
                MenuItem::query()->where('menu_id', $locked->id)->whereIn('id', $deleteIds)->delete();
            }
            $locked->touch();

            return (string) optional($locked->refresh()->updated_at)->toISOString();
        });
    }

    private function write(Menu $menu, array $items, ?int $parentId, $existing, array &$kept): void
    {
        foreach (array_values($items) as $order => $item) {
            $id = filled($item['id'] ?? null) ? (int) $item['id'] : null;
            if ($id && ! $existing->has($id)) {
                throw ValidationException::withMessages(['items' => 'Menu item does not belong to this menu.']);
            }
            $record = $id ? $existing->get($id) : new MenuItem;
            $type = $item['model_type'] ?? 'url';
            if ($type !== 'url' && blank($item['model_id'] ?? null)) {
                throw ValidationException::withMessages(['items' => 'Linked menu item needs a target.']);
            }
            $targetModel = MenuTargetRegistry::model($type);
            if ($type !== 'url' && (! $targetModel || ! $targetModel::query()->whereKey($item['model_id'])->exists())) {
                throw ValidationException::withMessages(['items' => 'Menu target does not exist.']);
            }
            $record->fill(['menu_id' => $menu->id, 'parent_id' => $parentId, 'title' => $item['title'], 'url' => $type === 'url' ? $this->urls->normalize($item['url'] ?? null) : MenuTargetRegistry::url($type, (int) $item['model_id']), 'model_type' => $type, 'model_id' => $type === 'url' ? null : (int) $item['model_id'], 'sort_order' => $order])->save();
            $kept[] = $record->id;
            $this->write($menu, $item['children'] ?? [], $record->id, $existing, $kept);
        }
    }
}
