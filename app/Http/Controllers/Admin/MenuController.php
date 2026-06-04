<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::withCount('allItems')->latest()->get();
        return Inertia::render('Admin/Menu/Index', [
            'menus' => $menus,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Menu/Edit', [
            'menu' => null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255|unique:menus,location',
        ]);

        $menu = Menu::create($request->only('name', 'location'));

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu đã được tạo.');
    }

    public function edit(Menu $menu)
    {
        $menu->load(['items.allChildren']);

        return Inertia::render('Admin/Menu/Edit', [
            'menu' => $menu,
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255|unique:menus,location,' . $menu->id,
        ]);

        $menu->update($request->only('name', 'location'));

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu đã được cập nhật.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menu đã được xóa.');
    }

    public function saveItems(Request $request, Menu $menu)
    {
        $request->validate([
            'items' => 'present|array',
            'items.*.title' => 'required|string|max:255',
            'items.*.url' => 'nullable|string|max:255',
            'items.*.model_type' => 'nullable|string|max:255',
            'items.*.model_id' => 'nullable|integer',
            'items.*.children' => 'nullable|array',
        ]);

        $menu->allItems()->delete();

        $this->saveMenuItems($menu, $request->input('items'), null);

        return back()->with('success', 'Menu items đã được lưu.');
    }

    private function saveMenuItems(Menu $menu, array $items, ?int $parentId, int &$order = 0): void
    {
        foreach ($items as $item) {
            $menuItem = $menu->allItems()->create([
                'parent_id' => $parentId,
                'title' => $item['title'],
                'url' => $item['url'] ?? null,
                'model_type' => $item['model_type'] ?? null,
                'model_id' => $item['model_id'] ?? null,
                'sort_order' => $order++,
            ]);

            if (!empty($item['children'])) {
                $this->saveMenuItems($menu, $item['children'], $menuItem->id, $order);
            }
        }
    }
}
