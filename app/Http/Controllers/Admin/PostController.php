<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('category:id,name')
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Post/Index', [
            'posts' => $posts,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create()
    {
        $categories = PostCategory::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Post/Edit', [
            'post' => null,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'post_category_id' => 'nullable|exists:post_categories,id',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $post = Post::create($data);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Bài viết đã được tạo.');
    }

    public function edit(Post $post)
    {
        $categories = PostCategory::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/Post/Edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $post->id,
            'post_category_id' => 'nullable|exists:post_categories,id',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $post->update($data);

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Bài viết đã được cập nhật.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được xóa.');
    }
}
