<?php

namespace App\Services\Admin\Ai;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

final class AiConfigurationService
{
    private const DB_KEYS = [
        'content.api_key' => 'ai.content.api_key',
        'content.base_url' => 'ai.content.base_url',
        'content.wire_api' => 'ai.content.wire_api',
        'content.model' => 'ai.content.model',
        'content.max_tokens' => 'ai.content.max_tokens',
        'image.api_key' => 'ai.image.api_key',
        'image.base_url' => 'ai.image.base_url',
        'image.model' => 'ai.image.model',
        'image.quality' => 'ai.image.quality',
    ];

    /** @return array<string, mixed> */
    public function get(): array
    {
        return [
            'content' => [
                'api_key' => $this->secret('content.api_key', (string) Config::get('services.ai.content.api_key', '')),
                'base_url' => $this->value('content.base_url', (string) Config::get('services.ai.content.base_url')),
                'wire_api' => $this->value('content.wire_api', (string) Config::get('services.ai.content.wire_api')),
                'model' => $this->value('content.model', (string) Config::get('services.ai.content.model')),
                'max_tokens' => (int) $this->value('content.max_tokens', (string) Config::get('services.ai.content.max_tokens', 4000)),
            ],
            'image' => [
                'api_key' => $this->secret('image.api_key', (string) Config::get('services.ai.image.api_key', '')),
                'base_url' => $this->value('image.base_url', (string) Config::get('services.ai.image.base_url')),
                'model' => $this->value('image.model', (string) Config::get('services.ai.image.model')),
                'quality' => $this->value('image.quality', (string) Config::get('services.ai.image.quality')),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function publicSnapshot(): array
    {
        $config = $this->get();

        return [
            'content' => [
                'base_url' => $config['content']['base_url'],
                'wire_api' => $config['content']['wire_api'],
                'model' => $config['content']['model'],
                'max_tokens' => $config['content']['max_tokens'],
                'has_api_key' => filled($config['content']['api_key']),
                'key_hint' => $this->hint($config['content']['api_key']),
            ],
            'image' => [
                'base_url' => $config['image']['base_url'],
                'model' => $config['image']['model'],
                'quality' => $config['image']['quality'],
                'has_api_key' => filled($config['image']['api_key']),
                'key_hint' => $this->hint($config['image']['api_key']),
            ],
        ];
    }

    public function save(array $data): void
    {
        DB::transaction(function () use ($data): void {
            foreach (['content', 'image'] as $provider) {
                $prefix = $provider.'_';
                foreach (['base_url', 'model', 'quality', 'wire_api', 'max_tokens'] as $field) {
                    $input = $prefix.$field;
                    if (array_key_exists($input, $data) && $data[$input] !== null) {
                        Setting::set('ai.'.$provider.'.'.$field, trim((string) $data[$input]), 'ai');
                    }
                }

                $keyInput = $prefix.'api_key';
                if (($data[$prefix.'clear_api_key'] ?? false) === true) {
                    Setting::set('ai.'.$provider.'.api_key', '', 'secret');
                } elseif (filled($data[$keyInput] ?? null)) {
                    Setting::set('ai.'.$provider.'.api_key', Crypt::encryptString(trim((string) $data[$keyInput])), 'secret');
                }
            }
        });
    }

    public function assertSecureBaseUrl(string $baseUrl): void
    {
        $scheme = strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME));
        if ($scheme !== 'https') {
            throw new \InvalidArgumentException('AI provider phải dùng HTTPS.');
        }
    }

    private function value(string $key, string $default): string
    {
        $value = Setting::query()->where('key', self::DB_KEYS[$key])->value('value');

        return filled($value) ? (string) $value : $default;
    }

    private function secret(string $key, string $default): string
    {
        $encrypted = Setting::query()->where('key', self::DB_KEYS[$key])->value('value');
        if (! filled($encrypted)) {
            return $default;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    private function hint(string $key): ?string
    {
        return filled($key) ? '••••••'.substr($key, -4) : null;
    }
}
