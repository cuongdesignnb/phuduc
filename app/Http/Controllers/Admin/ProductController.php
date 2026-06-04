<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('images')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Product/Index', [
            'products' => $products,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Product/Edit', [
            'product' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'specifications' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product = Product::create($data);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Sản phẩm đã được tạo.');
    }

    public function edit(Product $product)
    {
        $product->load('images');

        return Inertia::render('Admin/Product/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'specifications' => 'nullable|array',
            'status' => 'nullable|in:active,inactive',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Sản phẩm đã được cập nhật.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            \Storage::disk('public')->delete($image->image_path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa.');
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'file|image|max:10240',
            'is_360' => 'nullable|boolean',
        ]);

        $maxOrder = $product->images()->max('sort_order') ?? -1;

        foreach ($request->file('images') as $file) {
            $path = $file->store('products/' . $product->id, 'public');
            $product->images()->create([
                'image_path' => $path,
                'is_360' => $request->boolean('is_360'),
                'sort_order' => ++$maxOrder,
            ]);
        }

        return back()->with('success', 'Ảnh đã được tải lên.');
    }

    public function addImageFromMedia(Request $request, Product $product)
    {
        $request->validate([
            'media_id' => 'required|integer|exists:media_libraries,id',
            'is_360' => 'nullable|boolean',
        ]);

        $media = MediaLibrary::findOrFail($request->media_id);
        $maxOrder = $product->images()->max('sort_order') ?? -1;

        $product->images()->create([
            'image_path' => $media->file_path,
            'is_360' => $request->boolean('is_360'),
            'sort_order' => ++$maxOrder,
        ]);

        return back()->with('success', 'Ảnh đã được thêm từ Media Library.');
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }
        \Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Ảnh đã được xóa.');
    }

    public function reorderImages(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:product_images,id',
        ]);

        foreach ($request->order as $index => $id) {
            ProductImage::where('id', $id)->where('product_id', $product->id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Thứ tự ảnh đã được cập nhật.');
    }
}
