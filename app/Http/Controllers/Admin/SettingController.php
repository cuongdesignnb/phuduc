<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveSettingsRequest;
use App\Services\Admin\Content\AdminSettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(AdminSettingService $settings): Response
    {
        return Inertia::render('Admin/Setting/Index', $settings->page(request()->user()));
    }

    public function save(SaveSettingsRequest $request, AdminSettingService $settings): RedirectResponse
    {
        $version = $settings->save($request->validated());

        return back()->with('success', 'Cài đặt đã được lưu.')->with('admin_version', $version);
    }
}
