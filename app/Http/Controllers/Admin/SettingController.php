<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('key')->get()
            ->groupBy(fn($s) => explode('.', $s->key)[0]);

        return Inertia::render('Admin/Setting/Index', [
            'settings' => $settings,
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:255',
            'settings.*.value' => 'nullable|string',
            'settings.*.type' => 'nullable|string|in:text,textarea,image,json,boolean,font,color',
        ]);

        foreach ($request->settings as $item) {
            Setting::set($item['key'], $item['value'] ?? '', $item['type'] ?? 'text');
        }

        return back()->with('success', 'Cài đặt đã được lưu.');
    }
}
