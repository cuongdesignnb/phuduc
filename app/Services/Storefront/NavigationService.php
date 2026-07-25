<?php

namespace App\Services\Storefront;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Navigation\MenuTargetResolver;
use Illuminate\Support\Collection;
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
            ->with(['allItems' => fn ($query) => $query->orderBy('sort_order')])
            ->get()
            ->keyBy('location');

        $roots = collect(['header', 'footer'])->mapWithKeys(function (string $location) use ($menus): array {
            $items = $menus->get($location)?->allItems ?? collect();

            return [$location => $this->tree($items)];
        });
        $allItems = $roots->flatMap(fn ($items) => $this->flatten($items));
        $targetMap = $this->targets->resolveForItems($allItems);

        return $this->navigation = $roots->map(fn ($items) => $items->map(fn (MenuItem $item) => $this->present($item, $targetMap))->filter()->values()->all())->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function present(MenuItem $item, array $targetMap): ?array
    {
        $type = (string) ($item->model_type ?: 'url');
        $url = $type === 'url' ? (filled($item->url) ? $item->url : null) : ($targetMap["{$type}:{$item->model_id}"] ?? null);
        if ($url === null) {
            return null;
        }

        return [
            'id' => $item->id,
            'label' => $item->title,
            'url' => $url,
            'children' => $item->allChildren->map(fn (MenuItem $child) => $this->present($child, $targetMap))->filter()->values()->all(),
        ];
    }

    private function flatten(iterable $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = $item;
            $result = [...$result, ...$this->flatten($item->allChildren)];
        }

        return $result;
    }

    /** @return Collection<int, MenuItem> */
    private function tree(Collection $items): Collection
    {
        $children = $items->groupBy(fn (MenuItem $item) => (string) ($item->parent_id ?? 'root'));
        $build = function (MenuItem $item) use (&$build, $children): MenuItem {
            $item->setRelation('allChildren', $children->get((string) $item->id, collect())->map($build)->values());

            return $item;
        };

        return $children->get('root', collect())->map($build)->values();
    }
}
