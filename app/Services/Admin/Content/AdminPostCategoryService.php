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
        $roots = PostCategory::query()->whereNull('parent_id')->withCount(['posts', 'children'])->with(['children' => fn ($q) => $q->withCount(['posts', 'children'])->with('allChildren')])->orderBy('name')->get();
        return $this->pages->envelope($user, 'admin_post_categories_index', 'Danh mục tin', [['label' => 'Danh mục tin', 'url' => route('admin.post-categories.index')]], ['items' => $roots->map(fn (PostCategory $category) => $this->tree($category))->values()->all()]);
    }

    public function editPage(User $user, ?PostCategory $category): array
    {
        $excluded = $category ? collect($this->descendantIds($category))->push($category->id)->all() : [];
        $parents = PostCategory::query()->whereNotIn('id', $excluded)->orderBy('name')->get(['id', 'name', 'parent_id'])->map(fn (PostCategory $parent) => ['id' => $parent->id, 'name' => $parent->name, 'parent_id' => $parent->parent_id])->all();
        $label = $category ? 'Sửa danh mục' : 'Thêm danh mục';
        return $this->pages->envelope($user, 'admin_post_categories_edit', $label, [['label' => 'Danh mục tin', 'url' => route('admin.post-categories.index')], ['label' => $label, 'url' => $category ? route('admin.post-categories.edit', $category) : null]], ['category' => $category ? $this->item($category) : null, 'parents' => $parents]);
    }

    public function store(array $data): PostCategory { $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']); $this->assertParent($data['parent_id'] ?? null, null); return PostCategory::create($data); }
    public function update(PostCategory $category, array $data): PostCategory { $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $category->id); $this->assertParent($data['parent_id'] ?? null, $category); $category->update($data); return $category->refresh(); }
    public function destroy(PostCategory $category): void
    {
        if ($category->children()->exists() || $category->posts()->exists()) throw ValidationException::withMessages(['category' => 'Danh mục còn danh mục con hoặc bài viết và chưa thể xóa.']);
        $category->delete();
    }

    private function tree(PostCategory $category): array { return [...$this->item($category), 'children' => $category->allChildren->map(fn ($child) => $this->tree($child))->values()->all()]; }
    private function item(PostCategory $category): array { return ['id' => $category->id, 'name' => $category->name, 'slug' => $category->slug, 'description' => $category->description, 'posts_count' => (int) ($category->posts_count ?? $category->posts()->count()), 'children_count' => (int) ($category->children_count ?? $category->children()->count()), 'edit_url' => route('admin.post-categories.edit', $category), 'delete_url' => route('admin.post-categories.destroy', $category)]; }
    private function descendantIds(PostCategory $category): array { $ids = []; foreach ($category->children()->get() as $child) { $ids[] = $child->id; $ids = [...$ids, ...$this->descendantIds($child)]; } return $ids; }
    private function assertParent(?int $parentId, ?PostCategory $category): void { if (! $parentId) return; if ($category && $parentId === $category->id) throw ValidationException::withMessages(['parent_id' => 'Danh mục không thể là cha của chính nó.']); $seen = []; $current = PostCategory::find($parentId); while ($current) { if (in_array($current->id, $seen, true)) throw ValidationException::withMessages(['parent_id' => 'Cây danh mục chứa vòng lặp.']); $seen[] = $current->id; if ($category && $current->id === $category->id) throw ValidationException::withMessages(['parent_id' => 'Không thể chọn danh mục con làm danh mục cha.']); $current = $current->parent; } }
    private function uniqueSlug(string $value, ?int $ignoreId = null): string { $base = Str::slug($value) ?: 'danh-muc'; $slug = $base; $suffix = 2; while (PostCategory::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) $slug = $base.'-'.($suffix++); return $slug; }
}
