<?php

namespace App\Services\Storefront;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichHtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'blockquote', 'br', 'caption', 'code', 'div', 'em', 'figcaption', 'figure',
        'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'img', 'li', 'ol', 'p', 'pre', 's', 'span',
        'strong', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    private const GLOBAL_ATTRIBUTES = ['class', 'style', 'title'];

    private const TAG_ATTRIBUTES = [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'li' => ['data-list'],
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

            if ($name === 'style') {
                $safeStyle = $allowed ? $this->sanitizeStyle($value) : '';

                if ($safeStyle === '') {
                    $node->removeAttribute($attribute->name);
                } else {
                    $node->setAttribute($attribute->name, $safeStyle);
                }

                continue;
            }

            if (! $allowed || str_starts_with($name, 'on') || ! $this->hasAllowedUrl($name, $value)) {
                $node->removeAttribute($attribute->name);
            }
        }

        if ($node->tagName === 'a' && $node->hasAttribute('target')) {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function sanitizeStyle(string $style): string
    {
        $safeDeclarations = [];

        foreach (explode(';', $style) as $declaration) {
            [$property, $value] = array_pad(explode(':', $declaration, 2), 2, '');
            $property = strtolower(trim($property));
            $value = trim($value);

            if (in_array($property, ['color', 'background-color'], true) && $this->isAllowedColor($value)) {
                $safeDeclarations[] = $property.': '.$value;
            }

            if ($property === 'text-align' && in_array(strtolower($value), ['left', 'center', 'right', 'justify'], true)) {
                $safeDeclarations[] = 'text-align: '.strtolower($value);
            }
        }

        return implode('; ', array_unique($safeDeclarations));
    }

    private function isAllowedColor(string $value): bool
    {
        return (bool) preg_match(
            '/^(?:#[0-9a-f]{3,8}|(?:rgb|rgba)\(\s*(?:\d{1,3}%?\s*,\s*){2}\d{1,3}%?(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)|[a-z]{3,20})$/i',
            $value,
        );
    }

    private function hasAllowedUrl(string $name, string $value): bool
    {
        if (! in_array($name, ['href', 'src'], true)) {
            return true;
        }

        $decoded = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($decoded === '' || str_starts_with($decoded, '//')) {
            return false;
        }

        if ($name === 'src' && str_starts_with($decoded, '#')) {
            return false;
        }

        $schemeProbe = preg_replace('/[\x00-\x20\x7F]+/', '', $decoded) ?? '';
        $colonPosition = strpos($schemeProbe, ':');
        $pathDelimiterPosition = $this->firstDelimiterPosition($schemeProbe);

        if ($colonPosition !== false && ($pathDelimiterPosition === null || $colonPosition < $pathDelimiterPosition)) {
            $scheme = strtolower(substr($schemeProbe, 0, $colonPosition));
            $allowedSchemes = $name === 'href'
                ? ['http', 'https', 'mailto', 'tel']
                : ['http', 'https'];

            return in_array($scheme, $allowedSchemes, true);
        }

        return true;
    }

    private function firstDelimiterPosition(string $value): ?int
    {
        $positions = array_filter([
            strpos($value, '/'),
            strpos($value, '?'),
            strpos($value, '#'),
        ], fn ($position) => $position !== false);

        return $positions === [] ? null : min($positions);
    }
}
