<?php

namespace App\Services\Admin\Ai;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class AiProviderClient
{
    public function __construct(private readonly AiConfigurationService $configuration) {}

    /** @return array{text: string, usage: array<string, mixed>, provider: string, model: string} */
    public function text(array $messages, array $options = []): array
    {
        $config = $this->configuration->get()['content'];
        $this->configuration->assertSecureBaseUrl($config['base_url']);
        if (blank($config['api_key'])) {
            throw new \RuntimeException('Chưa cấu hình API key cho AI nội dung.');
        }

        $payload = [
            'model' => $config['model'],
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.35,
            'max_tokens' => min((int) ($options['max_tokens'] ?? $config['max_tokens']), 12000),
        ];
        if (($options['json'] ?? true) === true) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $wire = $config['wire_api'] === 'responses' ? 'responses' : 'chat/completions';
        if ($wire === 'responses') {
            $payload = [
                'model' => $config['model'],
                'input' => $messages,
                'max_output_tokens' => $payload['max_tokens'],
            ];
        }

        $response = $this->request($config['base_url'], $config['api_key'])->post('/'.$wire, $payload);
        if ($response->failed()) {
            throw new \RuntimeException('AI nội dung trả về lỗi HTTP '.$response->status().'.');
        }

        $body = $response->json();
        $text = $this->extractText($body);
        if (blank($text)) {
            throw new \RuntimeException('AI nội dung không trả về văn bản hợp lệ.');
        }

        return [
            'text' => $text,
            'usage' => is_array($body['usage'] ?? null) ? $body['usage'] : [],
            'provider' => (string) parse_url($config['base_url'], PHP_URL_HOST),
            'model' => (string) $config['model'],
        ];
    }

    /** @return array{items: list<array{url?: string, b64_json?: string}>, provider: string, model: string} */
    public function image(string $prompt, int $count = 1): array
    {
        $config = $this->configuration->get()['image'];
        $this->configuration->assertSecureBaseUrl($config['base_url']);
        if (blank($config['api_key'])) {
            throw new \RuntimeException('Chưa cấu hình API key cho AI hình ảnh.');
        }

        $response = $this->request($config['base_url'], $config['api_key'])->post('/images/generations', [
            'model' => $config['model'],
            'prompt' => $prompt,
            'n' => min(max($count, 1), 4),
            'size' => '1536x1024',
            'quality' => $config['quality'],
            'response_format' => 'b64_json',
        ]);
        if ($response->failed()) {
            throw new \RuntimeException('AI hình ảnh trả về lỗi HTTP '.$response->status().'.');
        }

        $items = collect($response->json('data', []))->map(fn ($item) => [
            'url' => is_string($item['url'] ?? null) ? $item['url'] : null,
            'b64_json' => is_string($item['b64_json'] ?? null) ? $item['b64_json'] : null,
        ])->filter(fn (array $item) => filled($item['url']) || filled($item['b64_json']))->values()->all();

        if ($items === []) {
            throw new \RuntimeException('AI hình ảnh không trả về dữ liệu ảnh.');
        }

        return [
            'items' => $items,
            'provider' => (string) parse_url($config['base_url'], PHP_URL_HOST),
            'model' => (string) $config['model'],
        ];
    }

    private function request(string $baseUrl, string $key): PendingRequest
    {
        return Http::baseUrl(rtrim($baseUrl, '/'))
            ->withToken($key)
            ->acceptJson()
            ->timeout(120)
            ->retry(1, 250);
    }

    private function extractText(mixed $body): string
    {
        if (! is_array($body)) {
            return '';
        }
        foreach (['output_text', 'text'] as $key) {
            if (is_string($body[$key] ?? null)) {
                return $body[$key];
            }
        }
        if (is_string($body['choices'][0]['message']['content'] ?? null)) {
            return $body['choices'][0]['message']['content'];
        }
        if (is_string($body['choices'][0]['text'] ?? null)) {
            return $body['choices'][0]['text'];
        }

        foreach ($body['output'] ?? [] as $item) {
            foreach ($item['content'] ?? [] as $content) {
                if (is_string($content['text'] ?? null)) {
                    return $content['text'];
                }
            }
        }

        return '';
    }
}
