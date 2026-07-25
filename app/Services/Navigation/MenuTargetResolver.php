<?php

namespace App\Services\Navigation;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

final class MenuTargetResolver
{
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
