<?php

namespace App\Services\Admin\Content;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Navigation\MenuTargetResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuItemSyncService
{
    public function __construct(private readonly AdminConcurrencyService $concurrency, private readonly AdminUrlService $urls, private readonly MenuTargetResolver $targets) {}

    public function sync(Menu $menu, array $items, ?string $version): string
    {
        return DB::transaction(function () use ($menu, $items, $version): string {
            $locked = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $this->concurrency->assertVersion($version, $locked, 'Menu đã được cập nhật ở phiên khác. Vui lòng tải lại.');
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
            if (array_key_exists('id', $item) && $item['id'] !== null && ! is_int($item['id'])) {
                throw ValidationException::withMessages(['items' => 'ID mục menu phải là số nguyên.']);
            }
            $id = $item['id'] ?? null;
            if ($id && ! $existing->has($id)) {
                throw ValidationException::withMessages(['items' => 'Mục menu không thuộc menu này.']);
            }
            $record = $id ? $existing->get($id) : new MenuItem;
            $type = $item['model_type'] ?? 'url';
            if ($type !== 'url' && (! array_key_exists('model_id', $item) || ! is_int($item['model_id']))) {
                throw ValidationException::withMessages(['items' => 'ID đích menu phải là số nguyên.']);
            }
            if ($type !== 'url' && blank($item['model_id'] ?? null)) {
                throw ValidationException::withMessages(['items' => 'Mục menu liên kết cần có đối tượng đích.']);
            }
            $targetModel = MenuTargetRegistry::model($type);
            if ($type !== 'url' && (! $targetModel || ! $targetModel::query()->whereKey($item['model_id'])->exists())) {
                throw ValidationException::withMessages(['items' => 'Đối tượng đích của menu không tồn tại.']);
            }
            $record->fill(['menu_id' => $menu->id, 'parent_id' => $parentId, 'title' => $item['title'], 'url' => $type === 'url' ? $this->urls->normalize($item['url'] ?? null) : $this->targets->resolve($type, $item['model_id']), 'model_type' => $type, 'model_id' => $type === 'url' ? null : $item['model_id'], 'sort_order' => $order])->save();
            $kept[] = $record->id;
            $this->write($menu, $item['children'] ?? [], $record->id, $existing, $kept);
        }
    }
}
