<?php

namespace App\Services\Storefront;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Schema;

class NavigationService
{
    /**
     * @var array{header: array<int, mixed>, footer: array<int, mixed>}|null
     */
    private ?array $navigation = null;

    /**
     * @return array{header: array<int, mixed>, footer: array<int, mixed>}
     */
    public function get(): array
    {
        if ($this->navigation !== null) {
            return $this->navigation;
        }

        if (! Schema::hasTable('menus')) {
            return $this->navigation = ['header' => [], 'footer' => []];
        }

        $menus = Menu::query()
            ->whereIn('location', ['header', 'footer'])
            ->with(['items.allChildren' => fn ($query) => $query->orderBy('sort_order')])
            ->get()
            ->keyBy('location');

        return $this->navigation = [
            'header' => $menus->get('header')?->items->map(fn (MenuItem $item) => $this->present($item))->values()->all() ?? [],
            'footer' => $menus->get('footer')?->items->map(fn (MenuItem $item) => $this->present($item))->values()->all() ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function present(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'label' => $item->title,
            'url' => $item->url ?: '#',
            'children' => $item->allChildren->map(fn (MenuItem $child) => $this->present($child))->values()->all(),
        ];
    }
}
