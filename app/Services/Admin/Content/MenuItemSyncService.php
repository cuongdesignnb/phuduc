<?php

namespace App\Services\Admin\Content;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Admin\AdminConcurrencyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MenuItemSyncService
{
    public function __construct(private readonly AdminConcurrencyService $concurrency) {}

    public function sync(Menu $menu, array $items, ?string $version): void
    {
        DB::transaction(function () use ($menu, $items, $version): void {
            $locked = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            $this->concurrency->assertVersion($version, $locked, 'Menu đã được cập nhật ở phiên khác. Vui lòng tải lại.');
            $existing = $locked->allItems()->get()->keyBy('id');
            $kept = [];
            $order = 0;
            $this->write($locked, $items, null, $existing, $kept, $order);
            $deleteIds = $existing->keys()->diff($kept)->values();
            if ($deleteIds->isNotEmpty()) MenuItem::query()->where('menu_id', $locked->id)->whereIn('id', $deleteIds)->delete();
            $locked->touch();
        });
    }

    private function write(Menu $menu, array $items, ?int $parentId, $existing, array &$kept, int &$order): void
    {
        foreach ($items as $item) {
            $id = filled($item['id'] ?? null) ? (int) $item['id'] : null;
            if ($id && ! $existing->has($id)) throw ValidationException::withMessages(['items' => 'Mục menu không thuộc menu đang chỉnh sửa.']);
            $record = $id ? $existing->get($id) : new MenuItem;
            $type = $item['model_type'] ?? 'url';
            if ($type !== 'url' && blank($item['model_id'] ?? null)) throw ValidationException::withMessages(['items' => 'Mục liên kết phải có đối tượng đích.']);
            $targetModel = MenuTargetRegistry::model($type);
            if ($type !== 'url' && (! $targetModel || ! $targetModel::query()->whereKey($item['model_id'])->exists())) throw ValidationException::withMessages(['items' => 'Đối tượng đích không tồn tại.']);
            $record->fill(['menu_id' => $menu->id, 'parent_id' => $parentId, 'title' => $item['title'], 'url' => $type === 'url' ? ($item['url'] ?? null) : MenuTargetRegistry::url($type, (int) $item['model_id']), 'model_type' => $type, 'model_id' => $type === 'url' ? null : (int) $item['model_id'], 'sort_order' => $order++])->save();
            $kept[] = $record->id;
            $this->write($menu, $item['children'] ?? [], $record->id, $existing, $kept, $order);
        }
    }
}
