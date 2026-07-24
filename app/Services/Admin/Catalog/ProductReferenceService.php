<?php

namespace App\Services\Admin\Catalog;

use App\Models\HomeSection;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;

class ProductReferenceService
{
    /** @return array<int, array{type: string, count: int}> */
    public function references(Product $product): array
    {
        $references = [
            'order_items' => OrderItem::query()->where('product_id', $product->id)->count(),
            'reviews' => Review::query()->where('product_id', $product->id)->count(),
            'home_content' => HomeSection::query()->get(['settings_json'])->filter(fn (HomeSection $section) => $this->containsId($section->settings_json, $product->id))->count(),
            'menu_items' => MenuItem::query()->where('model_type', 'product')->where('model_id', $product->id)->count(),
        ];

        return collect($references)->filter()->map(fn (int $count, string $type) => ['type' => $type, 'count' => $count])->values()->all();
    }

    public function canDelete(Product $product): bool { return $this->references($product) === []; }

    private function containsId(mixed $value, int $id): bool
    {
        if (is_array($value)) {
            foreach ($value as $child) {
                if ($this->containsId($child, $id)) return true;
            }
            return false;
        }

        return is_int($value) && $value === $id;
    }
}
