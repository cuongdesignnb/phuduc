<?php

namespace App\Services\Admin\Content;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;

final class MenuTargetRegistry
{
    /** @return array<string, array{label: string}> */
    public static function all(): array
    {
        return ['url' => ['label' => 'URL tự do'], 'product' => ['label' => 'Sản phẩm'], 'post' => ['label' => 'Bài viết'], 'category' => ['label' => 'Danh mục tin']];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function model(string $type): ?string
    {
        return ['product' => Product::class, 'post' => Post::class, 'category' => PostCategory::class][$type] ?? null;
    }

    public static function url(string $type, int $id): ?string
    {
        return match ($type) {
            'product' => route('products.show', Product::findOrFail($id)->slug), 'post' => route('news.show', Post::findOrFail($id)->slug), 'category' => route('news.index', ['category' => PostCategory::findOrFail($id)->slug]), default => null
        };
    }
}
