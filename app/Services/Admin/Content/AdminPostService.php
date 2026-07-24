<?php

namespace App\Services\Admin\Content;

use App\Models\HomeSection;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\AdminPresentationService;
use App\Services\Admin\Media\MediaAssetService;
use App\Services\Admin\Media\MediaReferenceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminPostService
{
    public function __construct(
        private readonly AdminPostPresentationService $presentation,
        private readonly AdminPageService $pages,
        private readonly AdminPresentationService $adminPresentation,
        private readonly AdminConcurrencyService $concurrency,
        private readonly MediaReferenceService $mediaReferences,
        private readonly MediaAssetService $assets,
    ) {}

    public function index(User $user, array $filters): array
    {
        $paginator = Post::query()->with('category:id,name')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($q) use ($search): void {
                $value = '%'.addcslashes($search, '%_\\').'%';
                $q->where('title', 'like', $value)->orWhere('summary', 'like', $value);
            }))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()->paginate(15)->withQueryString();
        $mediaIds = $this->mediaReferences->idsForPaths($paginator->getCollection()->pluck('featured_image')->all());

        return $this->pages->envelope($user, 'admin_posts_index', 'Posts', [['label' => 'Posts', 'url' => route('admin.posts.index')]], [
            'items' => $paginator->getCollection()->map(fn (Post $post) => $this->presentation->item($post, $mediaIds[$this->mediaReferences->normalize($post->featured_image)] ?? null))->values()->all(),
            'pagination' => $this->adminPresentation->pagination($paginator),
            'filters' => ['search' => $filters['search'] ?? '', 'status' => $filters['status'] ?? ''],
        ]);
    }

    public function editPage(User $user, ?Post $post, array $categories): array
    {
        $label = $post ? 'Edit post' : 'Add post';

        return $this->pages->envelope($user, 'admin_posts_edit', $label, [
            ['label' => 'Posts', 'url' => route('admin.posts.index')],
            ['label' => $label, 'url' => $post ? route('admin.posts.edit', $post) : null],
        ], [
            'post' => $post ? $this->presentation->edit($post) : null,
            'categories' => $categories,
            'statuses' => [['key' => 'draft', 'label' => 'Draft'], ['key' => 'published', 'label' => 'Published']],
        ]);
    }

    public function store(array $data): Post
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['title']);
        $data['featured_image'] = $this->path($data['featured_media_id'] ?? null);
        unset($data['featured_media_id']);

        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data): Post {
            $locked = Post::query()->lockForUpdate()->findOrFail($post->id);
            $this->concurrency->assertVersion($data['version'] ?? null, $locked, 'Post was updated in another session. Reload and try again.');
            $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['title'], $locked->id);
            $data['featured_image'] = $this->path($data['featured_media_id'] ?? null);
            unset($data['featured_media_id'], $data['version']);
            $locked->update($data);

            return $locked->refresh();
        });
    }

    public function destroy(Post $post): void
    {
        $refs = [];
        $home = HomeSection::query()->get(['settings_json'])->filter(fn (HomeSection $section) => $this->containsId($section->settings_json, $post->id))->count();
        $menu = MenuItem::query()->where('model_type', 'post')->where('model_id', $post->id)->count();
        if ($home) {
            $refs[] = ['type' => 'home_content', 'count' => $home];
        }
        if ($menu) {
            $refs[] = ['type' => 'menu_items', 'count' => $menu];
        }
        if ($refs) {
            throw ValidationException::withMessages(['post' => 'Post is referenced and cannot be deleted.', 'references' => $refs]);
        }
        $post->delete();
    }

    private function path(?int $mediaId): ?string
    {
        return $mediaId ? $this->assets->requireImage($mediaId)->file_path : null;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'bai-viet';
        $slug = $base;
        $suffix = 2;
        while (Post::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.($suffix++);
        }

        return $slug;
    }

    private function containsId(mixed $value, int $id): bool
    {
        if (is_array($value)) {
            foreach ($value as $child) {
                if ($this->containsId($child, $id)) {
                    return true;
                }
            }
        }

        return is_int($value) && $value === $id;
    }
}
