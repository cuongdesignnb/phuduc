<?php

namespace App\Services\Admin\Content;

use App\Models\Setting;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\Media\MediaReferenceService;
use App\Services\Storefront\ThemeTokenService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminSettingService
{
    public function __construct(private readonly AdminPageService $pages, private readonly AdminConcurrencyService $concurrency, private readonly MediaReferenceService $mediaReferences) {}

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

        return $this->pages->envelope($user, 'admin_settings_index', 'Settings', [['label' => 'Settings', 'url' => route('admin.settings.index')]], [
            'groups' => $groups,
            'group_labels' => ['site' => 'Website', 'about' => 'Giới thiệu', 'seo' => 'SEO', 'appearance' => 'Giao diện'],
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
                throw ValidationException::withMessages(['settings' => 'Setting is not registered.']);
            }
            $value = $definition['type'] === 'image' && isset($item['media_id']) ? (string) $this->mediaReferences->resolvePath((int) $item['media_id']) : (string) ($item['value'] ?? '');
            $this->validate($definition, $value);
        }

        return DB::transaction(function () use ($payload, $registry): string {
            $stored = Setting::query()->whereIn('key', array_keys($registry))->lockForUpdate()->get();
            $this->concurrency->assertFingerprint($payload['version'] ?? null, $this->version($stored), 'Settings changed in another session. Reload and try again.');
            foreach ($payload['settings'] as $item) {
                $definition = $registry[$item['key']];
                $value = $definition['type'] === 'image' && isset($item['media_id']) ? $this->mediaReferences->resolvePath((int) $item['media_id']) : ($item['value'] ?? '');
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
            throw ValidationException::withMessages(['settings' => $definition['label'].' exceeds its maximum length.']);
        }
        if ($definition['type'] === 'color' && $value !== '' && ! preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            throw ValidationException::withMessages(['settings' => 'Color must be a valid hex value.']);
        }
        if ($definition['type'] === 'font' && $value !== '' && ! array_key_exists($value, ThemeTokenService::FONT_OPTIONS)) {
            throw ValidationException::withMessages(['settings' => 'Font is not allowed.']);
        }
        if ($definition['type'] === 'image' && $value !== '' && ! $this->mediaReferences->idForPath($value)) {
            throw ValidationException::withMessages(['settings' => 'Image must be selected from Media Library.']);
        }
        if ($definition['key'] === 'site.email' && $value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['settings' => 'Email is invalid.']);
        }
        if (in_array($definition['key'], ['site.facebook', 'site.youtube', 'site.zalo', 'site.map_embed'], true) && $value !== '' && ! filter_var($value, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages(['settings' => 'URL is invalid.']);
        }
    }
}
