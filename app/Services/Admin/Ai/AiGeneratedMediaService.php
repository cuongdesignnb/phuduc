<?php

namespace App\Services\Admin\Ai;

use App\Models\MediaLibrary;
use App\Models\MediaFolder;
use App\Services\Admin\Media\AdminImageStorageService;
use App\Services\Storefront\MediaUrlService;
use Illuminate\Support\Facades\Http;

final class AiGeneratedMediaService
{
    public function __construct(
        private readonly AiProviderClient $provider,
        private readonly AdminImageStorageService $storage,
        private readonly MediaUrlService $mediaUrl,
    ) {}

    /** @return array{media: MediaLibrary, url: string, alt: string, caption: string} */
    public function generate(string $prompt, string $alt, string $caption, string $directory): array
    {
        $generated = $this->provider->image($prompt, 1)['items'][0];
        $contents = $this->contents($generated);
        $stored = $this->storage->storeGenerated($contents, $alt, $directory);
        $folder = MediaFolder::query()->firstOrCreate(['parent_id' => null, 'name' => 'AI generated']);
        $media = MediaLibrary::create([
            'folder_id' => $folder->id,
            'file_path' => $stored['path'],
            'mime_type' => $stored['mime_type'],
            'size' => $stored['size'],
            'file_name' => $stored['file_name'],
            'alt_text' => mb_substr(trim($alt), 0, 255),
            'caption' => mb_substr(trim($caption), 0, 255),
        ]);

        return [
            'media' => $media,
            'url' => $this->mediaUrl->resolve($media->file_path),
            'alt' => $media->alt_text,
            'caption' => $media->caption,
        ];
    }

    private function contents(array $item): string
    {
        if (filled($item['b64_json'] ?? null)) {
            $decoded = base64_decode((string) $item['b64_json'], true);
            if ($decoded === false) {
                throw new \RuntimeException('Dữ liệu ảnh base64 không hợp lệ.');
            }

            return $decoded;
        }

        $url = (string) ($item['url'] ?? '');
        if (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['https'], true)) {
            throw new \RuntimeException('URL ảnh AI không hợp lệ.');
        }
        $response = Http::timeout(60)->get($url);
        if ($response->failed() || blank($response->body())) {
            throw new \RuntimeException('Không thể tải ảnh AI về máy chủ.');
        }
        if (strlen($response->body()) > 10 * 1024 * 1024) {
            throw new \RuntimeException('Ảnh AI vượt quá giới hạn 10 MB.');
        }

        return $response->body();
    }
}
