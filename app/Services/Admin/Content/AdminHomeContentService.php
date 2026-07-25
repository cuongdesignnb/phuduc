<?php

namespace App\Services\Admin\Content;

use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\Media\MediaAssetService;
use App\Services\Admin\Media\MediaReferenceService;
use App\Support\Homepage\HomeSectionRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminHomeContentService
{
    private const BUSINESS_FIELDS = ['title', 'subtitle', 'description', 'image', 'icon', 'url'];

    private const METADATA_FIELDS = ['tone', 'avatar_text'];

    public function __construct(private readonly AdminPageService $pages, private readonly MediaReferenceService $mediaReferences, private readonly AdminConcurrencyService $concurrency, private readonly MediaAssetService $assets) {}

    public function page(User $user): array
    {
        $sections = HomeSection::query()->with('items')->orderBy('sort_order')->orderBy('id')->get();
        $definitions = HomeSectionRegistry::definitions();
        $paths = $sections->flatMap(fn (HomeSection $section) => [$section->settings_json['image'] ?? null, ...$section->items->pluck('image')->all()])->all();
        $mediaIds = $this->mediaReferences->idsForPaths($paths);
        $items = collect($definitions)->map(function (array $definition, string $key) use ($sections, $mediaIds): array {
            $section = $sections->firstWhere('key', $key);

            $config = array_replace_recursive($definition['defaults'], $section?->settings_json ?? []);
            if (! empty($config['image'])) {
                $config['image_media_id'] = $mediaIds[$this->mediaReferences->normalize($config['image'])] ?? null;
                $config['image_is_legacy'] = $config['image_media_id'] === null;
            }

            return ['id' => $section?->id, 'key' => $key, 'type' => $section?->type ?: $definition['type'], 'enabled' => $section?->is_enabled ?? true, 'sort_order' => $section?->sort_order ?? 999, 'variant' => $section?->variant ?: $definition['allowed_variants'][0], 'heading' => ['eyebrow' => $section?->settings_json['eyebrow'] ?? null, 'title' => $section?->title ?? $definition['label'], 'subtitle' => $section?->subtitle, 'description' => $section?->description], 'config' => $config, 'items' => $section?->items->map(function (HomeSectionItem $item) use ($mediaIds): array {
                $mediaId = $mediaIds[$this->mediaReferences->normalize($item->image)] ?? null;

                return ['id' => $item->id, 'title' => $item->title, 'subtitle' => $item->subtitle, 'description' => $item->description, 'image' => $item->image, 'media_id' => $mediaId, 'image_is_legacy' => filled($item->image) && $mediaId === null, 'icon' => $item->icon, 'url' => $item->url, 'metadata' => $item->metadata_json ?? [], 'enabled' => $item->is_active, 'sort_order' => $item->sort_order];
            })->values()->all() ?? []];
        })->sortBy('sort_order')->values()->all();

        return $this->pages->envelope($user, 'admin_home_content_index', 'Nội dung trang chủ', [['label' => 'Nội dung trang chủ', 'url' => route('admin.home-content.index')]], ['sections' => $items, 'registry' => $definitions, 'version' => $this->version($sections, $sections->flatMap->items)]);
    }

    public function productLookup(array $filters): array
    {
        $ids = $this->ids($filters['ids'] ?? []);
        $query = Product::query()->select(['id', 'name', 'sku', 'status'])->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'));
        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        // Product lookup cap: limit(20).
        return $query->orderBy('name')->limit($this->limit($filters))->get()->map(fn (Product $product) => ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku, 'status' => $product->status])->all();
    }

    public function postLookup(array $filters): array
    {
        $ids = $this->ids($filters['ids'] ?? []);
        $query = Post::query()->select(['id', 'title', 'status'])->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', '%'.addcslashes($search, '%_\\').'%'));
        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        // Post lookup cap: limit(20).
        return $query->orderBy('title')->limit($this->limit($filters))->get()->map(fn (Post $post) => ['id' => $post->id, 'title' => $post->title, 'status' => $post->status])->all();
    }

    public function save(array $payload): string
    {
        return DB::transaction(function () use ($payload): string {
            $currentSections = HomeSection::query()->orderBy('id')->lockForUpdate()->get();
            $currentItems = $currentSections->isEmpty() ? collect() : HomeSectionItem::query()->whereIn('home_section_id', $currentSections->pluck('id'))->orderBy('id')->lockForUpdate()->get();
            $this->concurrency->assertFingerprint($payload['version'] ?? null, $this->version($currentSections, $currentItems), 'Nội dung trang chủ đã thay đổi ở phiên khác. Vui lòng tải lại.');
            foreach ($payload['sections'] as $sectionData) {
                $this->saveSection($sectionData);
            }

            $sections = HomeSection::query()->orderBy('id')->get();
            $items = $sections->isEmpty() ? collect() : HomeSectionItem::query()->whereIn('home_section_id', $sections->pluck('id'))->orderBy('id')->get();

            return $this->version($sections, $items);
        });
    }

    private function saveSection(array $data): void
    {
        $definition = HomeSectionRegistry::get($data['key']);
        if (! $definition) {
            throw ValidationException::withMessages(['sections' => 'Section chưa được đăng ký trong registry.']);
        }
        $section = HomeSection::query()->firstOrNew(['key' => $data['key']]);
        $config = array_replace($section->settings_json ?? [], $data['config'] ?? []);
        if (array_key_exists('image', $config) && filled($config['image']) && empty($config['image_media_id']) && ! $this->isUnchangedLegacyPath($section->settings_json['image'] ?? null, $config['image'])) {
            throw ValidationException::withMessages(['sections' => 'Ảnh trang chủ phải được chọn từ Media bằng ID.']);
        }
        if (array_key_exists('image_media_id', $config)) {
            if ($config['image_media_id']) {
                $config['image'] = $this->assets->requireImage((int) $config['image_media_id'])->file_path;
            } elseif (! $this->isUnchangedLegacyPath($section->settings_json['image'] ?? null, $config['image'] ?? null)) {
                $config['image'] = null;
            }
            unset($config['image_media_id']);
        }
        $section->fill(['type' => $definition['type'], 'title' => $data['heading']['title'] ?? null, 'subtitle' => $data['heading']['subtitle'] ?? null, 'description' => $data['heading']['description'] ?? null, 'variant' => $data['variant'], 'is_enabled' => $data['enabled'], 'sort_order' => $data['sort_order'], 'settings_json' => array_intersect_key($config, array_flip($definition['config_keys']))])->save();
        if ($definition['supports_items']) {
            $this->syncItemsForCompatibility($section, $data['items'] ?? [], $definition['item_fields'], true);
        }
    }

    public function syncItemsForCompatibility(HomeSection $section, array $items, array $allowedFields, bool $strictMedia = false): void
    {
        $submittedIds = collect($items)->pluck('id')->filter()->map(fn ($id) => (int) $id)->values();
        if ($submittedIds->isNotEmpty() && HomeSectionItem::query()->where('home_section_id', $section->id)->whereIn('id', $submittedIds)->count() !== $submittedIds->unique()->count()) {
            throw ValidationException::withMessages(['sections' => 'Item được gửi không thuộc section này.']);
        }
        $business = array_values(array_intersect($allowedFields, self::BUSINESS_FIELDS));
        $metadata = array_values(array_intersect($allowedFields, self::METADATA_FIELDS));
        $kept = [];
        foreach ($items as $data) {
            $item = filled($data['id'] ?? null) ? HomeSectionItem::query()->where('home_section_id', $section->id)->findOrFail($data['id']) : new HomeSectionItem;
            if ($strictMedia && array_key_exists('image', $data) && filled($data['image']) && empty($data['media_id']) && ! $this->isUnchangedLegacyPath($item->image, $data['image'])) {
                throw ValidationException::withMessages(['sections' => 'Ảnh mục trang chủ phải được chọn từ Media bằng ID.']);
            }
            if (array_key_exists('media_id', $data)) {
                if ($data['media_id']) {
                    $data['image'] = $this->assets->requireImage((int) $data['media_id'])->file_path;
                } elseif (! $this->isUnchangedLegacyPath($item->image, $data['image'] ?? null)) {
                    $data['image'] = null;
                }
            }
            $item->fill(['home_section_id' => $section->id, 'section_key' => $section->key, ...Arr::only($data, $business), 'metadata_json' => Arr::only($data['metadata'] ?? [], $metadata), 'is_active' => $data['enabled'], 'sort_order' => $data['sort_order']])->save();
            $kept[] = $item->id;
        }
        HomeSectionItem::query()->where('home_section_id', $section->id)->when($kept !== [], fn ($q) => $q->whereNotIn('id', $kept))->delete();
    }

    private function version(Collection $sections, Collection $items): string
    {
        $parts = $sections->sortBy('id')->map(fn (HomeSection $section) => 'section:'.$section->id.':'.$section->updated_at?->toISOString())->all();
        foreach ($items->sortBy('id') as $item) {
            $parts[] = 'item:'.$item->id.':'.$item->updated_at?->toISOString();
        }

        return sha1(implode('|', $parts));
    }

    private function ids(array $ids): array
    {
        return collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
    }

    private function limit(array $filters): int
    {
        return min(20, max(1, (int) ($filters['limit'] ?? 20)));
    }

    private function isUnchangedLegacyPath(?string $current, ?string $submitted): bool
    {
        return filled($current) && $current === $submitted && $this->mediaReferences->idForPath($current) === null;
    }
}
