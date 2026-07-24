<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveMenuItemsRequest;
use App\Http\Requests\Admin\StoreMenuRequest;
use App\Http\Requests\Admin\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\Admin\Content\AdminMenuService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(AdminMenuService $menus): Response
    {
        return Inertia::render('Admin/Menu/Index', $menus->index(request()->user()));
    }

    public function create(AdminMenuService $menus): Response
    {
        return Inertia::render('Admin/Menu/Edit', $menus->editPage(request()->user(), null));
    }

    public function store(StoreMenuRequest $request, AdminMenuService $menus): RedirectResponse
    {
        $menu = $menus->store($request->validated());

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu created.');
    }

    public function edit(Menu $menu, AdminMenuService $menus): Response
    {
        return Inertia::render('Admin/Menu/Edit', $menus->editPage(request()->user(), $menu));
    }

    public function update(UpdateMenuRequest $request, Menu $menu, AdminMenuService $menus): RedirectResponse
    {
        $menus->update($menu, $request->validated());

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu updated.');
    }

    public function destroy(Menu $menu, AdminMenuService $menus): RedirectResponse
    {
        $menus->destroy($menu);

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    public function saveItems(SaveMenuItemsRequest $request, Menu $menu, AdminMenuService $menus): RedirectResponse
    {
        $version = $menus->saveItems($menu, $request->validated('items'), $request->validated('version'));

        return back()->with('success', 'Menu items saved.')->with('admin_version', $version);
    }
}
