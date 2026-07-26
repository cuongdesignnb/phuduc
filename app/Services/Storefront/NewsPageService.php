<?php

namespace App\Services\Storefront;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class NewsPageService
{
    private const PER_PAGE = 12;

    public function __construct(
        private readonly PostPresentationService $posts,
        private readonly StorefrontSeoService $seo,
        private readonly RichHtmlSanitizer $sanitizer,
    ) {}

    /**
     * @param  array{search: ?string, category: ?string}  $filters
     * @return array<string, mixed>
     */
    public function index(array $filters): array
    {
        $selectedCategory = $filters['category']
            ? PostCategory::query()
                ->select(['id', 'name', 'slug'])
                ->where('slug', $filters['category'])
                ->firstOrFail()
            : null;

        $paginator = Post::query()
            ->select(['id', 'post_category_id', 'title', 'slug', 'summary', 'featured_image', 'status', 'created_at', 'updated_at'])
            ->where('status', 'published')
            ->with('category:id,name,slug')
            ->when($selectedCategory, fn (Builder $query) => $query->where('post_category_id', $selectedCategory->id))
            ->when($filters['search'], fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
                $keyword = $this->escapeLike($search);
                $query->whereRaw("title LIKE ? ESCAPE '\\\\'", ["%{$keyword}%"])
                    ->orWhereRaw("summary LIKE ? ESCAPE '\\\\'", ["%{$keyword}%"]);
            }))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
        $categories = PostCategory::query()
            ->select(['id', 'name', 'slug'])
            ->withCount(['posts' => fn (Builder $query) => $query->where('status', 'published')])
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->map(fn (PostCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'posts_count' => $category->posts_count,
            ])
            ->all();
        $breadcrumbs = [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Tin tức', 'url' => route('news.index')],
        ];
        $hasSearch = filled($filters['search']);

        return [
            'page' => [
                'type' => 'news_index',
                'seo' => $this->seo->meta([
                    'title' => 'Tin tức',
                    'description' => 'Tin tức và bài viết mới nhất',
                    'canonical' => route('news.index', array_filter(['category' => $selectedCategory?->slug])),
                    'robots' => $hasSearch ? 'noindex, follow' : 'index, follow',
                ]),
                'json_ld' => [$this->seo->breadcrumbJsonLd($breadcrumbs)],
                'breadcrumbs' => $breadcrumbs,
                'hero' => [
                    'eyebrow' => 'Tin tức',
                    'title' => 'Tin tức mới nhất',
                    'description' => 'Cập nhật kiến thức, giải pháp và câu chuyện vận hành xe điện.',
                ],
                'news' => [
                    'items' => $paginator->getCollection()->map(fn (Post $post) => $this->posts->card($post))->values()->all(),
                    'categories' => $categories,
                    'pagination' => $this->pagination($paginator),
                    'filters' => [
                        'search' => $filters['search'] ?? '',
                        'category' => $selectedCategory?->slug ?? '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function show(string $slug): array
    {
        $post = Post::query()
            ->select(['id', 'post_category_id', 'title', 'slug', 'summary', 'content', 'featured_image', 'status', 'created_at', 'updated_at'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with('category:id,name,slug')
            ->firstOrFail();
        $post->content = $this->sanitizer->sanitize($post->content);
        $presented = $this->posts->detail($post);
        $related = Post::query()
            ->select(['id', 'post_category_id', 'title', 'slug', 'summary', 'featured_image', 'status', 'created_at', 'updated_at'])
            ->where('status', 'published')
            ->whereKeyNot($post->id)
            ->when($post->post_category_id, fn (Builder $query) => $query->where('post_category_id', $post->post_category_id))
            ->with('category:id,name,slug')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(4)
            ->get()
            ->map(fn (Post $post) => $this->posts->card($post))
            ->values()
            ->all();
        $breadcrumbs = [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Tin tức', 'url' => route('news.index')],
            ['name' => $presented['title'], 'url' => route('news.show', $presented['slug'])],
        ];

        return [
            'page' => [
                'type' => 'news_detail',
                'seo' => $this->seo->meta([
                    'title' => $presented['title'],
                    'description' => mb_substr(strip_tags((string) ($presented['summary'] ?: $presented['content_html'])), 0, 160),
                    'ogImage' => $presented['image_url'],
                    'ogType' => 'article',
                    'canonical' => route('news.show', $presented['slug']),
                ]),
                'json_ld' => [
                    $this->seo->articleJsonLd($presented),
                    $this->seo->breadcrumbJsonLd($breadcrumbs),
                ],
                'breadcrumbs' => $breadcrumbs,
                'hero' => [
                    'eyebrow' => $presented['category']['name'] ?? 'Tin tức',
                    'title' => $presented['title'],
                    'description' => $presented['summary'],
                ],
                'post' => $presented,
                'related_posts' => $related,
            ],
        ];
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\%_');
    }

    /**
     * @return array<string, mixed>
     */
    private function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'links' => $paginator->linkCollection()->toArray(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
