<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::where('status', 'published')
            ->with('category:id,name,slug')
            ->when($request->category, fn($q, $slug) => $q->whereHas('category', fn($c) => $c->where('slug', $slug)))
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = PostCategory::withCount(['posts' => fn($q) => $q->where('status', 'published')])
            ->orderBy('name')
            ->get();

        return Inertia::render('Guest/News/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'filters' => $request->only('search', 'category'),
            'seo' => SeoService::meta([
                'title' => 'Tin tức',
                'description' => 'Tin tức và bài viết mới nhất',
            ]),
            'jsonLd' => SeoService::breadcrumbJsonLd([
                ['name' => 'Trang chủ', 'url' => url('/')],
                ['name' => 'Tin tức', 'url' => url('/tin-tuc')],
            ]),
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)
            ->where('status', 'published')
            ->with('category:id,name,slug')
            ->firstOrFail();

        $relatedPosts = Post::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->when($post->post_category_id, fn($q) => $q->where('post_category_id', $post->post_category_id))
            ->latest()
            ->limit(4)
            ->get();

        return Inertia::render('Guest/News/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'seo' => SeoService::meta([
                'title' => $post->title,
                'description' => mb_substr(strip_tags($post->summary ?? $post->content ?? ''), 0, 160),
                'ogImage' => $post->featured_image ? url("storage/{$post->featured_image}") : '',
                'ogType' => 'article',
            ]),
            'jsonLd' => [
                SeoService::articleJsonLd($post),
                SeoService::breadcrumbJsonLd([
                    ['name' => 'Trang chủ', 'url' => url('/')],
                    ['name' => 'Tin tức', 'url' => url('/tin-tuc')],
                    ['name' => $post->title],
                ]),
            ],
        ]);
    }
}
