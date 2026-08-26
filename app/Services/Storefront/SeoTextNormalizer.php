<?php

namespace App\Services\Storefront;

final class SeoTextNormalizer
{
    /**
     * Produce plain, UTF-8-safe text for META tags and JSON-LD.
     */
    public function normalize(?string $value, ?int $limit = null): string
    {
        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = trim($value);

        return $limit === null ? $value : mb_substr($value, 0, $limit);
    }

    /**
     * @return list<string>
     */
    public function issues(?string $value, bool $shouldBePlainText = false): array
    {
        $value = (string) $value;
        $issues = [];

        if ($value !== '' && preg_match('//u', $value) !== 1) {
            $issues[] = 'invalid-utf8';
        }

        if (str_contains($value, "\u{FFFD}")) {
            $issues[] = 'replacement-character';
        }

        if (preg_match('/(?:Ã.|Â.|Æ.|â€|ðŸ)/u', $value) === 1) {
            $issues[] = 'suspected-mojibake';
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value) === 1) {
            $issues[] = 'control-character';
        }

        if ($shouldBePlainText && $value !== strip_tags($value)) {
            $issues[] = 'contains-html';
        }

        if (preg_match('/https?:\/\/(?:localhost|127\.0\.0\.1)(?::\d+)?(?:\/|\b)/i', $value) === 1) {
            $issues[] = 'localhost-url';
        }

        return $issues;
    }
}
