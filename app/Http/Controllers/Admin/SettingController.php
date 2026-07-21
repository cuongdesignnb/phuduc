<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Storefront\ThemeTokenService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('key')->get()
            ->reject(fn (Setting $setting) => str_starts_with($setting->key, 'home.'))
            ->groupBy(fn ($s) => explode('.', $s->key)[0]);

        return Inertia::render('Admin/Setting/Index', [
            'settings' => $settings,
            'fontOptions' => ThemeTokenService::fontOptions(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:255',
            'settings.*.value' => 'nullable|string',
            'settings.*.type' => 'nullable|string|in:text,textarea,image,json,boolean,font,color',
        ]);

        if (collect($validated['settings'])->contains(fn (array $item) => str_starts_with($item['key'], 'home.'))) {
            throw ValidationException::withMessages([
                'settings' => 'Nội dung trang chủ chỉ được chỉnh tại mục Nội dung trang chủ.',
            ]);
        }

        foreach ($validated['settings'] as $item) {
            Setting::set($item['key'], $item['value'] ?? '', $item['type'] ?? 'text');
        }

        return back()->with('success', 'Cài đặt đã được lưu.');
    }
}
