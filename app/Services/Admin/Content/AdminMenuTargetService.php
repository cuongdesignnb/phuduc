<?php

namespace App\Services\Admin\Content;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;

final class AdminMenuTargetService
{
    public function products(array $filters): array
    {
        return Product::query()->select(['id', 'name', 'sku', 'status'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")))
            ->when($filters['ids'] ?? [], fn ($query, $ids) => $query->whereIn('id', $ids))
            ->orderBy('name')->limit($this->limit($filters))->get()
            ->map(fn (Product $item) => ['id' => $item->id, 'label' => $item->name.' ('.$item->sku.')', 'status' => $item->status])->all();
    }

    public function posts(array $filters): array
    {
        return Post::query()->select(['id', 'title', 'status'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($filters['ids'] ?? [], fn ($query, $ids) => $query->whereIn('id', $ids))
            ->orderBy('title')->limit($this->limit($filters))->get()
            ->map(fn (Post $item) => ['id' => $item->id, 'label' => $item->title, 'status' => $item->status])->all();
    }

    public function categories(array $filters): array
    {
        return PostCategory::query()->select(['id', 'name', 'slug'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($filters['ids'] ?? [], fn ($query, $ids) => $query->whereIn('id', $ids))
            ->orderBy('name')->limit($this->limit($filters))->get()
            ->map(fn (PostCategory $item) => ['id' => $item->id, 'label' => $item->name, 'status' => 'active'])->all();
    }

    private function limit(array $filters): int
    {
        return min(20, max(1, (int) ($filters['limit'] ?? 20)));
    }
}
