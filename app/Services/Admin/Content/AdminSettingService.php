<?php

namespace App\Services\Admin\Content;

use App\Models\Setting;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\Media\MediaAssetService;
use App\Services\Admin\Media\MediaReferenceService;
use App\Services\Storefront\ThemeTokenService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminSettingService
{
    public function __construct(private readonly AdminPageService $pages, private readonly AdminConcurrencyService $concurrency, private readonly MediaReferenceService $mediaReferences, private readonly MediaAssetService $assets) {}

    public function page(User $user): array
    {
        $registry = AdminSettingRegistry::all();
        $stored = Setting::query()->whereIn('key', array_keys($registry))->get()->keyBy('key');
        $imagePaths = collect($registry)->filter(fn (array $definition) => $definition['type'] === 'image')->map(fn (array $definition, string $key) => $stored->get($key)?->value)->filter()->all();
        $mediaIds = $this->mediaReferences->idsForPaths($imagePaths);
        $groups = collect($registry)->map(function (array $definition, string $key) use ($stored, $mediaIds): array {
            $setting = $stored->get($key);
            $value = $setting?->value ?? $definition['default'];

            return [...$definition, 'value' => $value, 'media_id' => $definition['type'] === 'image' ? ($mediaIds[$this->mediaReferences->normalize($value)] ?? null) : null, 'updated_at' => $setting?->updated_at?->toISOString()];
        })->groupBy('group')->map(fn (Collection $settings) => $settings->values()->all())->all();

        return $this->pages->envelope($user, 'admin_settings_index', 'Cài đặt', [['label' => 'Cài đặt', 'url' => route('admin.settings.index')]], [
            'groups' => $groups,
            'group_labels' => ['site' => 'Trang web', 'about' => 'Giới thiệu', 'seo' => 'SEO', 'appearance' => 'Giao diện'],
            'version' => $this->version($stored),
            'font_options' => ThemeTokenService::fontOptions(),
        ]);
    }

    public function save(array $payload): string
    {
        $registry = AdminSettingRegistry::all();
        foreach ($payload['settings'] as $item) {
            $definition = $registry[$item['key']] ?? null;
            if (! $definition) {
                throw ValidationException::withMessages(['settings' => 'Cài đặt chưa được đăng ký trong registry.']);
            }
            $value = $definition['type'] === 'image' && array_key_exists('media_id', $item) ? (string) ($item['media_id'] ? $this->assets->requireImage((int) $item['media_id'])->file_path : '') : (string) ($item['value'] ?? '');
            $this->validate($definition, $value);
        }

        return DB::transaction(function () use ($payload, $registry): string {
            $stored = Setting::query()->whereIn('key', array_keys($registry))->lockForUpdate()->get();
            $this->concurrency->assertFingerprint($payload['version'] ?? null, $this->version($stored), 'Cài đặt đã thay đổi ở phiên khác. Vui lòng tải lại.');
            foreach ($payload['settings'] as $item) {
                $definition = $registry[$item['key']];
                $value = $definition['type'] === 'image' && array_key_exists('media_id', $item) ? ($item['media_id'] ? $this->assets->requireImage((int) $item['media_id'])->file_path : null) : ($item['value'] ?? '');
                Setting::updateOrCreate(['key' => $item['key']], ['value' => $value, 'type' => $definition['type']]);
            }

            return $this->version(Setting::query()->whereIn('key', array_keys($registry))->get());
        });
    }

    private function version(Collection $settings): string
    {
        return sha1($settings->sortBy('key')->map(fn (Setting $setting) => $setting->key.':'.(string) $setting->value.':'.$setting->updated_at?->toISOString())->implode('|'));
    }

    private function validate(array $definition, string $value): void
    {
        if ($definition['max'] !== null && mb_strlen($value) > $definition['max']) {
            throw ValidationException::withMessages(['settings' => $definition['label'].' vượt quá độ dài tối đa cho phép.']);
        }
        if ($definition['type'] === 'color' && $value !== '' && ! preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            throw ValidationException::withMessages(['settings' => 'Màu phải là mã hex hợp lệ.']);
        }
        if ($definition['type'] === 'font' && $value !== '' && ! array_key_exists($value, ThemeTokenService::FONT_OPTIONS)) {
            throw ValidationException::withMessages(['settings' => 'Font đã chọn không được hỗ trợ.']);
        }
        if ($definition['type'] === 'image' && $value !== '' && ! $this->mediaReferences->idForPath($value)) {
            throw ValidationException::withMessages(['settings' => 'Ảnh phải được chọn từ Thư viện Media.']);
        }
        if ($definition['key'] === 'site.email' && $value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['settings' => 'Email không hợp lệ.']);
        }
        if (in_array($definition['key'], ['site.facebook', 'site.youtube', 'site.zalo', 'site.map_embed'], true) && $value !== '' && ! filter_var($value, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages(['settings' => 'URL không hợp lệ.']);
        }
    }
}
