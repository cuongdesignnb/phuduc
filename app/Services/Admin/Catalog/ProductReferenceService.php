<?php

namespace App\Services\Admin\Catalog;

use App\Models\HomeSection;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ProductReferenceService
{
    /** @return array<int, array<int, array{type: string, count: int}>> */
    public function forProducts(array $productIds): array
    {
        $ids = collect($productIds)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        $map = array_fill_keys($ids, []);
        if ($ids === []) {
            return $map;
        }
        $groups = [
            'order_items' => OrderItem::query()->whereIn('product_id', $ids)->select('product_id', DB::raw('count(*) as aggregate'))->groupBy('product_id')->pluck('aggregate', 'product_id'),
            'reviews' => Review::query()->whereIn('product_id', $ids)->select('product_id', DB::raw('count(*) as aggregate'))->groupBy('product_id')->pluck('aggregate', 'product_id'),
            'menu_items' => MenuItem::query()->where('model_type', 'product')->whereIn('model_id', $ids)->select('model_id', DB::raw('count(*) as aggregate'))->groupBy('model_id')->pluck('aggregate', 'model_id'),
        ];
        foreach ($groups as $type => $counts) {
            foreach ($counts as $id => $count) {
                $map[(int) $id][] = ['type' => $type, 'count' => (int) $count];
            }
        }
        $homeCounts = array_fill_keys($ids, 0);
        HomeSection::query()->select(['settings_json'])->cursor()->each(function (HomeSection $section) use (&$homeCounts): void {
            foreach (array_keys($homeCounts) as $id) {
                if ($this->containsId($section->settings_json, (int) $id)) {
                    $homeCounts[$id]++;
                }
            }
        });
        foreach ($homeCounts as $id => $count) {
            if ($count > 0) {
                $map[(int) $id][] = ['type' => 'home_content', 'count' => $count];
            }
        }

        return $map;
    }

    /** @return array<int, array{type: string, count: int}> */
    public function references(Product $product): array
    {
        return $this->forProducts([$product->id])[$product->id] ?? [];
    }

    public function canDelete(Product $product): bool
    {
        return $this->references($product) === [];
    }

    private function containsId(mixed $value, int $id): bool
    {
        if (is_array($value)) {
            foreach ($value as $child) {
                if ($this->containsId($child, $id)) {
                    return true;
                }
            }

return false;
        }

        return is_int($value) && $value === $id;
    }
}
