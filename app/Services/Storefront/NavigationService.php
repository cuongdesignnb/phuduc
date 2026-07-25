<?php

namespace App\Services\Storefront;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Navigation\MenuTargetResolver;
use Illuminate\Support\Facades\Schema;

class NavigationService
{
    public function __construct(private readonly MenuTargetResolver $targets) {}

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
            'header' => $menus->get('header')?->items->map(fn (MenuItem $item) => $this->present($item))->filter()->values()->all() ?? [],
            'footer' => $menus->get('footer')?->items->map(fn (MenuItem $item) => $this->present($item))->filter()->values()->all() ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function present(MenuItem $item): ?array
    {
        $url = $this->targets->resolve((string) ($item->model_type ?: 'url'), $item->model_id ? (int) $item->model_id : null, $item->url);
        if ($url === null) {
            return null;
        }

        return [
            'id' => $item->id,
            'label' => $item->title,
            'url' => $url,
            'children' => $item->allChildren->map(fn (MenuItem $child) => $this->present($child))->filter()->values()->all(),
        ];
    }
}
