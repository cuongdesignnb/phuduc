<?php

namespace App\Services\Admin\Content;

use App\Models\Setting;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\Media\MediaReferenceService;
use App\Services\Storefront\ThemeTokenService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminSettingService
{
    public function __construct(private readonly AdminPageService $pages, private readonly AdminConcurrencyService $concurrency, private readonly MediaReferenceService $mediaReferences) {}

    public function page(User $user): array
    {
        $registry = AdminSettingRegistry::all();
        $stored = Setting::query()->whereIn('key', array_keys($registry))->get()->keyBy('key');
        $groups = collect($registry)->map(function (array $definition, string $key) use ($stored): array {
            $setting = $stored->get($key);
            return [...$definition, 'value' => $setting?->value, 'media_id' => $definition['type'] === 'image' ? $this->mediaReferences->idForPath($setting?->value) : null, 'updated_at' => $setting?->updated_at?->toISOString()];
        })->groupBy('group')->map(fn ($settings) => $settings->values()->all())->all();
        $version = sha1($stored->map(fn (Setting $setting) => $setting->key.':'.$setting->updated_at?->toISOString())->implode('|'));
        return $this->pages->envelope($user, 'admin_settings_index', 'Settings', [['label' => 'Settings', 'url' => route('admin.settings.index')]], ['groups' => $groups, 'version' => $version, 'font_options' => ThemeTokenService::fontOptions()]);
    }

    public function save(array $payload): void
    {
        $registry = AdminSettingRegistry::all();
        foreach ($payload['settings'] as $item) {
            $definition = $registry[$item['key']] ?? null;
            if (! $definition || str_starts_with($item['key'], 'home.')) throw ValidationException::withMessages(['settings' => 'Setting is not registered.']);
            $value = ($definition['type'] ?? null) === 'image' && isset($item['media_id']) ? $this->mediaReferences->resolvePath((int) $item['media_id']) : (string) ($item['value'] ?? '');
            $this->validate($definition, $value);
        }
        DB::transaction(function () use ($payload, $registry): void {
            $stored = Setting::query()->whereIn('key', array_keys($registry))->get();
            $current = sha1($stored->map(fn (Setting $setting) => $setting->key.':'.$setting->updated_at?->toISOString())->implode('|'));
            $this->concurrency->assertFingerprint($payload['version'] ?? null, $current, 'Settings changed in another session. Reload and try again.');
            foreach ($payload['settings'] as $item) {
                $definition = $registry[$item['key']];
                $value = $definition['type'] === 'image' && isset($item['media_id']) ? $this->mediaReferences->resolvePath((int) $item['media_id']) : ($item['value'] ?? '');
                Setting::updateOrCreate(['key' => $item['key']], ['value' => $value, 'type' => $definition['type']]);
            }
        });
    }

    private function validate(array $definition, string $value): void
    {
        if ($definition['type'] === 'color' && $value !== '' && ! preg_match('/^#[0-9a-fA-F]{6}$/', $value)) throw ValidationException::withMessages(['settings' => 'Color must be a valid hex value.']);
        if ($definition['type'] === 'font' && $value !== '' && ! array_key_exists($value, ThemeTokenService::FONT_OPTIONS)) throw ValidationException::withMessages(['settings' => 'Font is not allowed.']);
        if ($definition['type'] === 'image' && $value !== '' && ! $this->mediaReferences->idForPath($value)) throw ValidationException::withMessages(['settings' => 'Image must be selected from Media Library.']);
        if ($definition['key'] === 'site.email' && $value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) throw ValidationException::withMessages(['settings' => 'Email is invalid.']);
        if (in_array($definition['key'], ['site.facebook', 'site.youtube', 'site.zalo', 'site.map_embed'], true) && $value !== '' && ! filter_var($value, FILTER_VALIDATE_URL)) throw ValidationException::withMessages(['settings' => 'URL is invalid.']);
    }
}
