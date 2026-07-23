<?php

namespace App\Services\Storefront;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichHtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'blockquote', 'br', 'caption', 'code', 'div', 'em', 'figcaption', 'figure',
        'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'img', 'li', 'ol', 'p', 'pre', 'span',
        'strong', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'ul',
    ];

    private const GLOBAL_ATTRIBUTES = ['class', 'title'];

    private const TAG_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan', 'scope'],
    ];

    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div data-root="true">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();

        $root = $document->getElementsByTagName('div')->item(0);

        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->cleanNode($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private function cleanNode(DOMNode $node): void
    {
        if ($node instanceof DOMElement && ! in_array($node->tagName, self::ALLOWED_TAGS, true) && $node->getAttribute('data-root') !== 'true') {
            $node->parentNode?->removeChild($node);

            return;
        }

        if ($node instanceof DOMElement) {
            $this->cleanAttributes($node);
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $this->cleanNode($child);
        }
    }

    private function cleanAttributes(DOMElement $node): void
    {
        foreach (iterator_to_array($node->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);
            $allowed = in_array($name, self::GLOBAL_ATTRIBUTES, true)
                || in_array($name, self::TAG_ATTRIBUTES[$node->tagName] ?? [], true);

            if (! $allowed || str_starts_with($name, 'on') || $this->hasUnsafeUrl($name, $value)) {
                $node->removeAttribute($attribute->name);
            }
        }

        if ($node->tagName === 'a' && $node->hasAttribute('target')) {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function hasUnsafeUrl(string $name, string $value): bool
    {
        return in_array($name, ['href', 'src'], true)
            && preg_match('/^\s*(javascript|vbscript|data):/i', $value) === 1;
    }
}
