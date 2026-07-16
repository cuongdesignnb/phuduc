<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveHomeContentRequest;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Post;
use App\Models\Product;
use App\Services\Storefront\MediaUrlService;
use App\Support\Homepage\HomeSectionRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class HomeContentController extends Controller
{
    public function index(MediaUrlService $mediaUrl)
    {
        $stored = HomeSection::query()->with('items')->orderBy('sort_order')->orderBy('id')->get()->keyBy('key');
        $sections = collect(HomeSectionRegistry::definitions())->map(function (array $definition, string $key) use ($stored) {
            $section = $stored->get($key);

            return [
                'id' => $section?->id,
                'key' => $key,
                'type' => $section?->type ?: $definition['type'],
                'enabled' => $section?->is_enabled ?? true,
                'sort_order' => $section?->sort_order ?? 999,
                'variant' => $section?->variant ?: $definition['allowed_variants'][0],
                'heading' => [
                    'eyebrow' => $section?->settings_json['eyebrow'] ?? null,
                    'title' => $section?->title ?? $definition['label'],
                    'subtitle' => $section?->subtitle,
                    'description' => $section?->description,
                ],
                'config' => array_replace_recursive($definition['defaults'], $section?->settings_json ?? []),
                'items' => $section?->items->map(fn (HomeSectionItem $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'subtitle' => $item->subtitle,
                    'description' => $item->description,
                    'image' => $item->image,
                    'icon' => $item->icon,
                    'url' => $item->url,
                    'metadata' => $item->metadata_json ?? [],
                    'enabled' => $item->is_active,
                    'sort_order' => $item->sort_order,
                ])->values()->all() ?? [],
            ];
        })->sortBy('sort_order')->values();

        return Inertia::render('Admin/HomeContent/Index', [
            'sections' => $sections,
            'registry' => HomeSectionRegistry::definitions(),
            'products' => Product::query()
                ->select(['id', 'name', 'sku', 'status'])
                ->with(['cardImage' => fn ($query) => $query->select([
                    'product_images.id',
                    'product_images.product_id',
                    'product_images.image_path',
                    'product_images.sort_order',
                    'product_images.is_360',
                ])])
                ->orderBy('name')
                ->get()
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'status' => $product->status,
                    'image_url' => $mediaUrl->resolve($product->cardImage?->image_path),
                ]),
            'posts' => Post::query()->select(['id', 'title', 'status'])->orderBy('title')->get(),
        ]);
    }

    public function save(SaveHomeContentRequest $request)
    {
        $sections = $request->validated('sections');

        DB::transaction(function () use ($sections): void {
            foreach ($sections as $sectionData) {
                $definition = HomeSectionRegistry::get($sectionData['key']);
                $section = HomeSection::query()->firstOrNew(['key' => $sectionData['key']]);
                $config = $sectionData['config'];

                if ($sectionData['key'] === 'energy_banner') {
                    $config['eyebrow'] = $sectionData['heading']['eyebrow'] ?? $config['eyebrow'] ?? null;
                }

                $section->fill([
                    'type' => $definition['type'],
                    'title' => $sectionData['heading']['title'] ?? null,
                    'subtitle' => $sectionData['heading']['subtitle'] ?? null,
                    'description' => $sectionData['heading']['description'] ?? null,
                    'variant' => $sectionData['variant'],
                    'is_enabled' => $sectionData['enabled'],
                    'sort_order' => $sectionData['sort_order'],
                    'settings_json' => $config,
                ])->save();

                if ($definition['supports_items']) {
                    $this->syncItems($section, $sectionData['items']);
                }
            }
        });

        return back()->with('success', 'Nội dung trang chủ đã được lưu.');
    }

    private function syncItems(HomeSection $section, array $items): void
    {
        $submittedIds = collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->values();

        if ($submittedIds->isNotEmpty()) {
            $ownedIds = HomeSectionItem::query()
                ->where('home_section_id', $section->id)
                ->whereIn('id', $submittedIds)
                ->pluck('id');

            if ($ownedIds->count() !== $submittedIds->unique()->count()) {
                throw ValidationException::withMessages(['sections' => 'Item không thuộc section được gửi lên.']);
            }
        }

        $keptIds = [];
        foreach ($items as $itemData) {
            $item = filled($itemData['id'] ?? null)
                ? HomeSectionItem::query()->where('home_section_id', $section->id)->findOrFail($itemData['id'])
                : new HomeSectionItem;

            $item->fill([
                'home_section_id' => $section->id,
                'section_key' => $section->key,
                'title' => $itemData['title'] ?? null,
                'subtitle' => $itemData['subtitle'] ?? null,
                'description' => $itemData['description'] ?? null,
                'image' => $itemData['image'] ?? null,
                'icon' => $itemData['icon'] ?? null,
                'url' => $itemData['url'] ?? null,
                'metadata_json' => $itemData['metadata'] ?? [],
                'is_active' => $itemData['enabled'],
                'sort_order' => $itemData['sort_order'],
            ])->save();

            $keptIds[] = $item->id;
        }

        HomeSectionItem::query()
            ->where('home_section_id', $section->id)
            ->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))
            ->delete();
    }
}
