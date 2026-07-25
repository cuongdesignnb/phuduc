<?php

namespace App\Services\Media;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Str;

final class RichContentMediaReferenceService
{
    /** @param list<?string> $paths @return array<string, list<array{type: string, count: int}>> */
    public function forPaths(array $paths): array
    {
        $normalized = collect($paths)->map(fn ($path) => $this->normalize($path))->filter()->unique()->values()->all();
        $result = array_fill_keys($normalized, []);
        if ($normalized === []) {
            return $result;
        }

        $counts = [];
        foreach (Post::query()->whereNotNull('content')->get(['id', 'content']) as $post) {
            foreach ($this->extractPaths($post->content) as $path) {
                if (isset($result[$path])) {
                    $counts[$path]['post_content'] = ($counts[$path]['post_content'] ?? 0) + 1;
                }
            }
        }
        foreach (Product::query()->whereNotNull('description')->get(['id', 'description']) as $product) {
            foreach ($this->extractPaths($product->description) as $path) {
                if (isset($result[$path])) {
                    $counts[$path]['product_description'] = ($counts[$path]['product_description'] ?? 0) + 1;
                }
            }
        }

        foreach ($counts as $path => $types) {
            foreach ($types as $type => $count) {
                $result[$path][] = ['type' => $type, 'count' => $count];
            }
        }

        return $result;
    }

    private function extractPaths(?string $html): array
    {
        if (! filled($html) || ! class_exists(\DOMDocument::class)) {
            return [];
        }

        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $paths = [];
        foreach ($document->getElementsByTagName('img') as $image) {
            $src = trim((string) $image->getAttribute('src'));
            if ($src !== '' && ! Str::startsWith(strtolower($src), ['data:', 'javascript:'])) {
                $paths[] = $this->normalize($src);
            }
        }

        return array_values(array_unique(array_filter($paths)));
    }

    private function normalize(?string $path): string
    {
        $path = (string) $path;
        $parsed = parse_url($path, PHP_URL_PATH) ?: $path;

        return ltrim(Str::replaceFirst('/storage/', '', $parsed), '/');
    }
}
