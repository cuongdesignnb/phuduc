<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FontController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120|mimes:ttf,otf,woff,woff2',
            'target' => 'required|in:heading,body',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();

        // Store font file
        $path = $file->storeAs('fonts', time().'_'.$originalName, 'public');

        // Save to settings
        $key = "font.custom_{$request->target}";
        Setting::set($key, $path, 'font_upload');

        // Also save font name for display
        $nameKey = "font.custom_{$request->target}_name";
        $fontName = pathinfo($originalName, PATHINFO_FILENAME);
        Setting::set($nameKey, $fontName, 'text');

        // Set font mode to custom if uploading
        Setting::set('font.mode', 'custom', 'text');

        return response()->json([
            'message' => 'Font uploaded successfully',
            'path' => $path,
            'name' => $fontName,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'target' => 'required|in:heading,body',
        ]);

        $key = "font.custom_{$request->target}";
        $nameKey = "font.custom_{$request->target}_name";

        $setting = Setting::where('key', $key)->first();
        if ($setting && $setting->value) {
            Storage::disk('public')->delete($setting->value);
        }

        Setting::where('key', $key)->delete();
        Setting::where('key', $nameKey)->delete();

        return response()->json(['message' => 'Font đã được xóa']);
    }
}
