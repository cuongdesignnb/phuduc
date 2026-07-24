<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttachProductMediaRequest;
use App\Http\Requests\Admin\ProductIndexRequest;
use App\Http\Requests\Admin\ReorderProductImagesRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Requests\Admin\UploadProductImagesRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Admin\Catalog\AdminProductService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request, AdminProductService $products): Response
    {
        return Inertia::render('Admin/Product/Index', $products->index($request->user(), $request->validated()));
    }

    public function create(AdminProductService $products): Response
    {
        return Inertia::render('Admin/Product/Edit', $products->editPage(request()->user(), null));
    }

    public function store(StoreProductRequest $request, AdminProductService $products): RedirectResponse
    {
        $product = $products->store($request->validated());

        return redirect()->route('admin.products.edit', $product)->with('success', 'Sản phẩm đã được tạo.');
    }

    public function edit(Product $product, AdminProductService $products): Response
    {
        return Inertia::render('Admin/Product/Edit', $products->editPage(request()->user(), $product));
    }

    public function update(UpdateProductRequest $request, Product $product, AdminProductService $products): RedirectResponse
    {
        $products->update($product, $request->validated());

        return redirect()->route('admin.products.edit', $product)->with('success', 'Sản phẩm đã được cập nhật.');
    }

    public function destroy(Product $product, AdminProductService $products): RedirectResponse
    {
        $products->destroy($product);

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa.');
    }

    public function uploadImages(UploadProductImagesRequest $request, Product $product, AdminProductService $products): RedirectResponse
    {
        $products->upload($product, $request->file('images', []), $request->boolean('is_360'));

        return back()->with('success', 'Ảnh đã được tải lên.');
    }

    public function addImageFromMedia(AttachProductMediaRequest $request, Product $product, AdminProductService $products): RedirectResponse
    {
        $products->attach($product, (int) $request->validated('media_id'), $request->boolean('is_360'));

        return back()->with('success', 'Ảnh đã được sao chép từ Media Library.');
    }

    public function deleteImage(Product $product, ProductImage $image, AdminProductService $products): RedirectResponse
    {
        $products->deleteImage($product, $image);

        return back()->with('success', 'Ảnh đã được xóa.');
    }

    public function reorderImages(ReorderProductImagesRequest $request, Product $product, AdminProductService $products): RedirectResponse
    {
        $products->reorder($product, $request->validated('order'));

        return back()->with('success', 'Thứ tự ảnh đã được cập nhật.');
    }
}
