<?php

namespace App\Services\Admin\Content;

use App\Models\Menu;

class AdminMenuPresentationService
{
    public function item(Menu $menu): array { return ['id' => $menu->id, 'name' => $menu->name, 'location' => $menu->location, 'location_label' => MenuLocationRegistry::all()[$menu->location]['label'] ?? $menu->location, 'items_count' => (int) ($menu->all_items_count ?? $menu->allItems()->count()), 'edit_url' => route('admin.menus.edit', $menu), 'delete_url' => route('admin.menus.destroy', $menu)]; }
    public function edit(Menu $menu): array { return ['id' => $menu->id, 'name' => $menu->name, 'location' => $menu->location, 'version' => (string) optional($menu->updated_at)->toISOString(), 'items' => $menu->items->map(fn ($item) => $this->tree($item))->values()->all()]; }
    private function tree($item): array { return ['id' => $item->id, 'client_key' => 'item-'.$item->id, 'title' => $item->title, 'url' => $item->url, 'model_type' => $item->model_type ?: 'url', 'model_id' => $item->model_id, 'children' => $item->allChildren->map(fn ($child) => $this->tree($child))->values()->all()]; }
}
