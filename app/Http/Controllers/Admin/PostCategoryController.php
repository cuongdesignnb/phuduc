<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostCategoryController extends Controller
{
    public function index()
    {
        $categories = PostCategory::withCount('posts')
            ->whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/PostCategory/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $parents = PostCategory::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Admin/PostCategory/Edit', [
            'category' => null,
            'parents' => $parents,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:post_categories,slug',
            'parent_id' => 'nullable|exists:post_categories,id',
            'description' => 'nullable|string',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        PostCategory::create($data);

        return redirect()->route('admin.post-categories.index')->with('success', 'Danh mục đã được tạo.');
    }

    public function edit(PostCategory $postCategory)
    {
        $parents = PostCategory::where('id', '!=', $postCategory->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/PostCategory/Edit', [
            'category' => $postCategory,
            'parents' => $parents,
        ]);
    }

    public function update(Request $request, PostCategory $postCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:post_categories,slug,' . $postCategory->id,
            'parent_id' => 'nullable|exists:post_categories,id',
            'description' => 'nullable|string',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $postCategory->update($data);

        return redirect()->route('admin.post-categories.index')->with('success', 'Danh mục đã được cập nhật.');
    }

    public function destroy(PostCategory $postCategory)
    {
        $postCategory->delete();
        return redirect()->route('admin.post-categories.index')->with('success', 'Danh mục đã được xóa.');
    }
}
