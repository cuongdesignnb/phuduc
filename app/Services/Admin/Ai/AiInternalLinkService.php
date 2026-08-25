<?php

namespace App\Services\Admin\Ai;

use App\Models\Post;
use App\Models\Product;
use DOMDocument;
use DOMElement;

final class AiInternalLinkService
{
    /** @return list<array{title: string, url: string, anchor: string, type: string}> */
    public function targets(?int $productId = null, ?int $categoryId = null): array
    {
        $products = Product::query()
            ->select(['id', 'name', 'slug'])
            ->where('status', 'active')
            ->when($productId, fn ($query) => $query->where('id', '!=', $productId))
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn (Product $product) => [
                'title' => $product->name,
                'url' => route('products.show', $product->slug),
                'anchor' => $product->name,
                'type' => 'product',
            ]);

        $posts = Post::query()
            ->select(['id', 'title', 'slug'])
            ->where('status', 'published')
            ->when($categoryId, fn ($query) => $query->where('post_category_id', $categoryId))
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->map(fn (Post $post) => [
                'title' => $post->title,
                'url' => route('news.show', $post->slug),
                'anchor' => $post->title,
                'type' => 'article',
            ]);

        return $products->concat($posts)->take(20)->values()->all();
    }

    public function apply(string $html, array $targets): string
    {
        if ($html === '' || $targets === []) {
            return $html;
        }

        $allowed = collect($targets)->keyBy(fn (array $target) => $this->normalizeUrl($target['url']))->all();
        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div data-root="true">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $document->getElementsByTagName('div')->item(0);
        if (! $root instanceof DOMElement) {
            return $html;
        }

        $used = [];
        foreach (iterator_to_array($root->getElementsByTagName('a')) as $anchor) {
            $href = $this->normalizeUrl($anchor->getAttribute('href'));
            if (! isset($allowed[$href]) || isset($used[$href])) {
                $this->unwrap($anchor);
                continue;
            }
            $used[$href] = true;
            $anchor->setAttribute('href', $allowed[$href]['url']);
            $anchor->setAttribute('rel', 'internal');
        }

        $missing = collect($allowed)->reject(fn (array $target, string $url) => isset($used[$url]))->take(3);
        if ($missing->isNotEmpty()) {
            $paragraph = $document->createElement('p');
            $paragraph->appendChild($document->createTextNode('Đọc thêm: '));
            foreach ($missing->values() as $index => $target) {
                if ($index > 0) {
                    $paragraph->appendChild($document->createTextNode(' · '));
                }
                $link = $document->createElement('a');
                $link->setAttribute('href', $target['url']);
                $link->setAttribute('rel', 'internal');
                $link->appendChild($document->createTextNode($target['anchor']));
                $paragraph->appendChild($link);
            }
            $root->appendChild($paragraph);
        }

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private function normalizeUrl(string $url): string
    {
        $parts = parse_url(trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if ($parts === false) {
            return '';
        }

        return strtolower((string) ($parts['path'] ?? '/')).(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    private function unwrap(DOMElement $anchor): void
    {
        $parent = $anchor->parentNode;
        if (! $parent) {
            return;
        }
        while ($anchor->firstChild) {
            $parent->insertBefore($anchor->firstChild, $anchor);
        }
        $parent->removeChild($anchor);
    }
}
