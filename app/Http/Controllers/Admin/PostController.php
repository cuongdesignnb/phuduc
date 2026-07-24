<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostIndexRequest;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Admin\Content\AdminPostService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(PostIndexRequest $request, AdminPostService $posts): Response { return Inertia::render('Admin/Post/Index', $posts->index($request->user(), $request->validated())); }
    public function create(AdminPostService $posts): Response { return Inertia::render('Admin/Post/Edit', $posts->editPage(request()->user(), null, $this->categories())); }
    public function store(StorePostRequest $request, AdminPostService $posts): RedirectResponse { $post = $posts->store($request->validated()); return redirect()->route('admin.posts.edit', $post)->with('success', 'Bài viết đã được tạo.'); }
    public function edit(Post $post, AdminPostService $posts): Response { return Inertia::render('Admin/Post/Edit', $posts->editPage(request()->user(), $post, $this->categories())); }
    public function update(UpdatePostRequest $request, Post $post, AdminPostService $posts): RedirectResponse { $posts->update($post, $request->validated()); return redirect()->route('admin.posts.edit', $post)->with('success', 'Bài viết đã được cập nhật.'); }
    public function destroy(Post $post, AdminPostService $posts): RedirectResponse { $posts->destroy($post); return redirect()->route('admin.posts.index')->with('success', 'Bài viết đã được xóa.'); }
    private function categories(): array { return PostCategory::query()->orderBy('name')->get(['id', 'name'])->map(fn (PostCategory $category) => ['id' => $category->id, 'name' => $category->name])->all(); }
}
