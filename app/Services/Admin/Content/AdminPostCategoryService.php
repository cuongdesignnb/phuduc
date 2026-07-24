<?php

namespace App\Services\Admin\Content;

use App\Models\PostCategory;
use App\Models\User;
use App\Services\Admin\AdminPageService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminPostCategoryService
{
    public function __construct(private readonly AdminPageService $pages) {}

    public function index(User $user): array
    {
        $categories = PostCategory::query()->withCount(['posts', 'children'])->orderBy('name')->get();
        $nodes = $categories->mapWithKeys(fn (PostCategory $category) => [$category->id => $this->item($category) + ['children' => []]])->all();
        foreach ($categories as $category) {
            if ($category->parent_id && isset($nodes[$category->parent_id])) {
                $nodes[$category->parent_id]['children'][] = &$nodes[$category->id];
            }
        }
        $roots = array_values(array_filter($nodes, fn (array $node) => $node['parent_id'] === null));

        return $this->pages->envelope($user, 'admin_post_categories_index', 'Danh muc tin', [['label' => 'Danh muc tin', 'url' => route('admin.post-categories.index')]], ['items' => $roots]);
    }

    public function editPage(User $user, ?PostCategory $category): array
    {
        $excluded = $category ? collect($this->descendantIds($category))->push($category->id)->all() : [];
        $parents = PostCategory::query()->when($excluded !== [], fn ($query) => $query->whereNotIn('id', $excluded))->orderBy('name')->get(['id', 'name', 'parent_id'])->map(fn (PostCategory $parent) => ['id' => $parent->id, 'name' => $parent->name, 'parent_id' => $parent->parent_id])->all();
        $label = $category ? 'Edit category' : 'Add category';

        return $this->pages->envelope($user, 'admin_post_categories_edit', $label, [['label' => 'Danh muc tin', 'url' => route('admin.post-categories.index')], ['label' => $label, 'url' => $category ? route('admin.post-categories.edit', $category) : null]], ['category' => $category ? $this->item($category) : null, 'parents' => $parents]);
    }

    public function store(array $data): PostCategory
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $this->assertParent($data['parent_id'] ?? null, null);

        return PostCategory::create($data);
    }

    public function update(PostCategory $category, array $data): PostCategory
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $category->id);
        $this->assertParent($data['parent_id'] ?? null, $category);
        $category->update($data);

        return $category->refresh();
    }

    public function destroy(PostCategory $category): void
    {
        if ($category->children()->exists() || $category->posts()->exists()) {
            throw ValidationException::withMessages(['category' => 'Category still has children or posts and cannot be deleted.']);
        }
        $category->delete();
    }

    private function item(PostCategory $category): array
    {
        return ['id' => $category->id, 'parent_id' => $category->parent_id, 'name' => $category->name, 'slug' => $category->slug, 'description' => $category->description, 'posts_count' => (int) ($category->posts_count ?? 0), 'children_count' => (int) ($category->children_count ?? 0), 'can_delete' => (int) ($category->posts_count ?? 0) === 0 && (int) ($category->children_count ?? 0) === 0, 'edit_url' => route('admin.post-categories.edit', $category), 'delete_url' => route('admin.post-categories.destroy', $category)];
    }

    private function descendantIds(PostCategory $category): array
    {
        $children = PostCategory::query()->get(['id', 'parent_id'])->groupBy('parent_id');
        $ids = [];
        $walk = function (int $parentId) use (&$walk, &$ids, $children): void {
            foreach ($children->get($parentId, collect()) as $child) {
                $ids[] = $child->id;
                $walk($child->id);
            }
        };
        $walk($category->id);

        return $ids;
    }

    private function assertParent(?int $parentId, ?PostCategory $category): void
    {
        if (! $parentId) {
            return;
        }
        if ($category && $parentId === $category->id) {
            throw ValidationException::withMessages(['parent_id' => 'A category cannot be its own parent.']);
        }
        $seen = [];
        $current = PostCategory::find($parentId);
        while ($current) {
            if (in_array($current->id, $seen, true)) {
                throw ValidationException::withMessages(['parent_id' => 'Category tree contains a cycle.']);
            }
            $seen[] = $current->id;
            if ($category && $current->id === $category->id) {
                throw ValidationException::withMessages(['parent_id' => 'A descendant cannot become its parent.']);
            }
            $current = $current->parent;
        }
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'danh-muc';
        $slug = $base;
        $suffix = 2;
        while (PostCategory::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.($suffix++);
        }

        return $slug;
    }
}
