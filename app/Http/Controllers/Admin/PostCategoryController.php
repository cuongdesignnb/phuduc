<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostCategoryRequest;
use App\Http\Requests\Admin\UpdatePostCategoryRequest;
use App\Models\PostCategory;
use App\Services\Admin\Content\AdminPostCategoryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostCategoryController extends Controller
{
    public function index(AdminPostCategoryService $categories): Response { return Inertia::render('Admin/PostCategory/Index', $categories->index(request()->user())); }
    public function create(AdminPostCategoryService $categories): Response { return Inertia::render('Admin/PostCategory/Edit', $categories->editPage(request()->user(), null)); }
    public function store(StorePostCategoryRequest $request, AdminPostCategoryService $categories): RedirectResponse { $categories->store($request->validated()); return redirect()->route('admin.post-categories.index')->with('success', 'Danh mục đã được tạo.'); }
    public function edit(PostCategory $postCategory, AdminPostCategoryService $categories): Response { return Inertia::render('Admin/PostCategory/Edit', $categories->editPage(request()->user(), $postCategory)); }
    public function update(UpdatePostCategoryRequest $request, PostCategory $postCategory, AdminPostCategoryService $categories): RedirectResponse { $categories->update($postCategory, $request->validated()); return redirect()->route('admin.post-categories.index')->with('success', 'Danh mục đã được cập nhật.'); }
    public function destroy(PostCategory $postCategory, AdminPostCategoryService $categories): RedirectResponse { $categories->destroy($postCategory); return redirect()->route('admin.post-categories.index')->with('success', 'Danh mục đã được xóa.'); }
}
