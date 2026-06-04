<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeContentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/HomeContent/Index', [
            'sections' => HomeSection::with('items')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'sections' => 'required|array',
            'sections.*.key' => 'required|string|max:255',
            'sections.*.title' => 'nullable|string|max:255',
            'sections.*.subtitle' => 'nullable|string|max:255',
            'sections.*.description' => 'nullable|string',
            'sections.*.is_enabled' => 'boolean',
            'sections.*.sort_order' => 'nullable|integer',
            'sections.*.settings_json' => 'nullable|array',
            'sections.*.items' => 'present|array',
            'sections.*.items.*.id' => 'nullable|integer',
            'sections.*.items.*.title' => 'nullable|string|max:255',
            'sections.*.items.*.subtitle' => 'nullable|string|max:255',
            'sections.*.items.*.description' => 'nullable|string',
            'sections.*.items.*.image' => 'nullable|string|max:255',
            'sections.*.items.*.icon' => 'nullable|string|max:255',
            'sections.*.items.*.url' => 'nullable|string|max:255',
            'sections.*.items.*.metadata_json' => 'nullable|array',
            'sections.*.items.*.is_active' => 'boolean',
            'sections.*.items.*.sort_order' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['sections'] as $sectionIndex => $sectionData) {
                $section = HomeSection::updateOrCreate(
                    ['key' => $sectionData['key']],
                    [
                        'title' => $sectionData['title'] ?? null,
                        'subtitle' => $sectionData['subtitle'] ?? null,
                        'description' => $sectionData['description'] ?? null,
                        'is_enabled' => (bool) ($sectionData['is_enabled'] ?? false),
                        'sort_order' => $sectionData['sort_order'] ?? $sectionIndex,
                        'settings_json' => $sectionData['settings_json'] ?? null,
                    ],
                );

                $keptIds = [];

                foreach ($sectionData['items'] as $itemIndex => $itemData) {
                    $payload = [
                        'section_key' => $section->key,
                        'title' => $itemData['title'] ?? null,
                        'subtitle' => $itemData['subtitle'] ?? null,
                        'description' => $itemData['description'] ?? null,
                        'image' => $itemData['image'] ?? null,
                        'icon' => $itemData['icon'] ?? null,
                        'url' => $itemData['url'] ?? null,
                        'metadata_json' => $itemData['metadata_json'] ?? null,
                        'is_active' => (bool) ($itemData['is_active'] ?? false),
                        'sort_order' => $itemData['sort_order'] ?? $itemIndex,
                    ];

                    if (! empty($itemData['id'])) {
                        $item = HomeSectionItem::where('section_key', $section->key)
                            ->where('id', $itemData['id'])
                            ->first();

                        if ($item) {
                            $item->update($payload);
                            $keptIds[] = $item->id;
                            continue;
                        }
                    }

                    $item = HomeSectionItem::create($payload);
                    $keptIds[] = $item->id;
                }

                HomeSectionItem::where('section_key', $section->key)
                    ->when(count($keptIds) > 0, fn($query) => $query->whereNotIn('id', $keptIds))
                    ->delete();
            }
        });

        return back()->with('success', 'Nội dung trang chủ đã được lưu.');
    }
}
