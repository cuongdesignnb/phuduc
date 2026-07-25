<?php

namespace App\Services\Navigation;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

final class MenuTargetResolver
{
    /** @param iterable<int, \App\Models\MenuItem> $items @return array<string, string> */
    public function resolveForItems(iterable $items): array
    {
        $ids = ['product' => [], 'post' => [], 'category' => []];
        foreach ($items as $item) {
            $type = (string) ($item->model_type ?: 'url');
            if (isset($ids[$type]) && $item->model_id !== null) {
                $ids[$type][] = (int) $item->model_id;
            }
        }
        foreach ($ids as $type => $values) {
            $ids[$type] = array_values(array_unique(array_filter($values)));
        }

        $models = [
            'product' => Product::query()->whereIn('id', $ids['product'])->get(['id', 'slug'])->keyBy('id'),
            'post' => Post::query()->whereIn('id', $ids['post'])->get(['id', 'slug'])->keyBy('id'),
            'category' => PostCategory::query()->whereIn('id', $ids['category'])->get(['id', 'slug'])->keyBy('id'),
        ];
        $map = [];
        foreach ($ids as $type => $values) {
            foreach ($values as $id) {
                $model = $models[$type]->get($id);
                if (! $model) {
                    Log::warning('menu_target_missing', ['model_type' => $type, 'model_id' => $id]);

                    continue;
                }
                $map["{$type}:{$id}"] = match ($type) {
                    'product' => route('products.show', $model->slug),
                    'post' => route('news.show', $model->slug),
                    'category' => route('news.index', ['category' => $model->slug]),
                };
            }
        }

        return $map;
    }

    public function resolve(string $type, ?int $id, ?string $customUrl = null): ?string
    {
        if ($type === 'url') {
            return filled($customUrl) ? $customUrl : null;
        }

        $url = match ($type) {
            'product' => ($model = $id ? Product::query()->find($id) : null) ? route('products.show', $model->slug) : null,
            'post' => ($model = $id ? Post::query()->find($id) : null) ? route('news.show', $model->slug) : null,
            'category' => ($model = $id ? PostCategory::query()->find($id) : null) ? route('news.index', ['category' => $model->slug]) : null,
            default => null,
        };

        if ($url === null) {
            Log::warning('menu_target_missing', ['model_type' => $type, 'model_id' => $id]);
        }

        return $url;
    }
}
