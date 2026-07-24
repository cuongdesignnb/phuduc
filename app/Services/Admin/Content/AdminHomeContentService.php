<?php

namespace App\Services\Admin\Content;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\Media\AdminMediaService;
use App\Services\Admin\Media\MediaReferenceService;
use App\Services\Storefront\MediaUrlService;
use App\Support\Homepage\HomeSectionRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminHomeContentService
{
    private const BUSINESS_FIELDS = ['title', 'subtitle', 'description', 'image', 'icon', 'url'];
    private const METADATA_FIELDS = ['tone', 'avatar_text'];

    public function __construct(private readonly AdminPageService $pages, private readonly AdminMediaService $media, private readonly MediaReferenceService $mediaReferences, private readonly MediaUrlService $mediaUrl, private readonly AdminConcurrencyService $concurrency) {}

    public function page(User $user): array
    {
        $sections = HomeSection::query()->with('items')->orderBy('sort_order')->orderBy('id')->get();
        $definitions = HomeSectionRegistry::definitions();
        $items = collect($definitions)->map(function (array $definition, string $key) use ($sections): array {
            $section = $sections->firstWhere('key', $key);
            return ['id' => $section?->id, 'key' => $key, 'type' => $section?->type ?: $definition['type'], 'enabled' => $section?->is_enabled ?? true, 'sort_order' => $section?->sort_order ?? 999, 'variant' => $section?->variant ?: $definition['allowed_variants'][0], 'heading' => ['eyebrow' => $section?->settings_json['eyebrow'] ?? null, 'title' => $section?->title ?? $definition['label'], 'subtitle' => $section?->subtitle, 'description' => $section?->description], 'config' => array_replace_recursive($definition['defaults'], $section?->settings_json ?? []), 'items' => $section?->items->map(fn (HomeSectionItem $item) => ['id' => $item->id, 'title' => $item->title, 'subtitle' => $item->subtitle, 'description' => $item->description, 'image' => $item->image, 'media_id' => $this->mediaReferences->idForPath($item->image), 'icon' => $item->icon, 'url' => $item->url, 'metadata' => $item->metadata_json ?? [], 'enabled' => $item->is_active, 'sort_order' => $item->sort_order])->values()->all() ?? []];
        })->sortBy('sort_order')->values()->all();
        $version = sha1($sections->map(fn (HomeSection $section) => $section->id.':'.$section->updated_at?->toISOString())->implode('|'));
        return $this->pages->envelope($user, 'admin_home_content_index', 'Nội dung trang chủ', [['label' => 'Nội dung trang chủ', 'url' => route('admin.home-content.index')]], ['sections' => $items, 'registry' => $definitions, 'version' => $version, 'media' => $this->media->picker([]), 'products' => $this->productLookup([]), 'posts' => $this->postLookup([])]);
    }

    public function productLookup(array $filters): array { return Product::query()->select(['id', 'name', 'sku', 'status'])->when($filters['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', '%'.addcslashes($s, '%_\\').'%'))->orderBy('name')->limit(20)->get()->map(fn (Product $product) => ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'status' => $product->status])->all(); }
    public function postLookup(array $filters): array { return Post::query()->select(['id', 'title', 'status'])->when($filters['search'] ?? null, fn ($q, $s) => $q->where('title', 'like', '%'.addcslashes($s, '%_\\').'%'))->orderBy('title')->limit(20)->get()->map(fn (Post $post) => ['id' => $post->id, 'title' => $post->title, 'status' => $post->status])->all(); }

    public function save(array $payload): void
    {
        $sections = $payload['sections'];
        DB::transaction(function () use ($sections, $payload): void {
            $current = HomeSection::query()->orderBy('id')->get();
            $currentVersion = sha1($current->map(fn (HomeSection $section) => $section->id.':'.$section->updated_at?->toISOString())->implode('|'));
            $this->concurrency->assertFingerprint($payload['version'] ?? null, $currentVersion, 'Nội dung trang chủ đã được cập nhật ở phiên khác. Vui lòng tải lại.');
            foreach ($sections as $sectionData) $this->saveSection($sectionData);
        });
    }

    private function saveSection(array $data): void
    {
        $definition = HomeSectionRegistry::get($data['key']);
        if (! $definition) throw ValidationException::withMessages(['sections' => 'Section không tồn tại trong registry.']);
        $config = $data['config'] ?? [];
        if (isset($config['image_media_id'])) { $config['image'] = $this->mediaReferences->resolvePath((int) $config['image_media_id']); unset($config['image_media_id']); }
        $section = HomeSection::query()->firstOrNew(['key' => $data['key']]);
        $section->fill(['type' => $definition['type'], 'title' => $data['heading']['title'] ?? null, 'subtitle' => $data['heading']['subtitle'] ?? null, 'description' => $data['heading']['description'] ?? null, 'variant' => $data['variant'], 'is_enabled' => $data['enabled'], 'sort_order' => $data['sort_order'], 'settings_json' => array_intersect_key($config, array_flip($definition['config_keys']))])->save();
        if ($definition['supports_items']) $this->syncItemsForCompatibility($section, $data['items'] ?? [], $definition['item_fields']);
    }

    public function syncItemsForCompatibility(HomeSection $section, array $items, array $allowedFields): void
    {
        $submittedIds = collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->values();
        if ($submittedIds->isNotEmpty() && HomeSectionItem::query()->where('home_section_id', $section->id)->whereIn('id', $submittedIds)->count() !== $submittedIds->unique()->count()) throw ValidationException::withMessages(['sections' => 'Item không thuộc section được gửi lên.']);
        $business = array_values(array_intersect($allowedFields, self::BUSINESS_FIELDS)); $metadata = array_values(array_intersect($allowedFields, self::METADATA_FIELDS)); $kept = [];
        foreach ($items as $data) {
            if (isset($data['media_id'])) $data['image'] = $this->mediaReferences->resolvePath((int) $data['media_id']);
            $item = filled($data['id'] ?? null) ? HomeSectionItem::query()->where('home_section_id', $section->id)->findOrFail($data['id']) : new HomeSectionItem;
            $item->fill(['home_section_id' => $section->id, 'section_key' => $section->key, ...Arr::only($data, $business), 'metadata_json' => Arr::only($data['metadata'] ?? [], $metadata), 'is_active' => $data['enabled'], 'sort_order' => $data['sort_order']])->save();
            $kept[] = $item->id;
        }
        HomeSectionItem::query()->where('home_section_id', $section->id)->when($kept !== [], fn ($q) => $q->whereNotIn('id', $kept))->delete();
    }
}
