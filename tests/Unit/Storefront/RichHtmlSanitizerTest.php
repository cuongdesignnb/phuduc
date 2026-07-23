<?php

namespace Tests\Unit\Storefront;

use App\Services\Storefront\RichHtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RichHtmlSanitizerTest extends TestCase
{
    public function test_allowed_html_is_retained_and_unsafe_html_is_removed(): void
    {
        $html = app(RichHtmlSanitizer::class)->sanitize(
            '<p>Safe <strong>text</strong></p><script>alert(1)</script><img src="javascript:alert(1)" onerror="alert(1)"><a href="https://example.com" onclick="alert(1)" target="_blank">Link</a><iframe src="https://example.com"></iframe><table><tr><td>Cell</td></tr></table>'
        );

        $this->assertStringContainsString('<strong>text</strong>', $html);
        $this->assertStringContainsString('<table>', $html);
        $this->assertStringContainsString('https://example.com', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unsafeAnchorUrls(): array
    {
        return [
            'simple javascript' => ['javascript:alert(1)'],
            'tab obfuscated javascript' => ['java&#x09;script:alert(1)'],
            'newline obfuscated javascript' => ['java&#x0A;script:alert(1)'],
            'carriage return obfuscated javascript' => ['jav&#x0D;ascript:alert(1)'],
            'data url' => ['data:text/html;base64,PHNjcmlwdD4='],
            'file url' => ['file:///etc/passwd'],
            'unknown scheme' => ['custom:payload'],
            'protocol relative url' => ['//example.com/path'],
        ];
    }

    #[DataProvider('unsafeAnchorUrls')]
    public function test_unsafe_anchor_urls_are_removed(string $url): void
    {
        $html = app(RichHtmlSanitizer::class)->sanitize('<a href="'.$url.'">Link</a>');

        $this->assertStringContainsString('<a>Link</a>', $html);
        $this->assertStringNotContainsString('href=', $html);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function safeAnchorUrls(): array
    {
        return [
            'https' => ['https://example.com/path'],
            'http' => ['http://example.com/path'],
            'mailto' => ['mailto:sales@example.com'],
            'tel' => ['tel:0900000000'],
            'relative' => ['products/item'],
            'root relative' => ['/products/item'],
            'fragment' => ['#details'],
        ];
    }

    #[DataProvider('safeAnchorUrls')]
    public function test_safe_anchor_urls_are_retained(string $url): void
    {
        $html = app(RichHtmlSanitizer::class)->sanitize('<a href="'.$url.'">Link</a>');

        $this->assertStringContainsString('href="'.$url.'"', $html);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unsafeImageUrls(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'tab obfuscated javascript' => ['java&#x09;script:alert(1)'],
            'data url' => ['data:image/png;base64,AAAA'],
            'file url' => ['file:///tmp/a.png'],
            'unknown scheme' => ['custom:image'],
            'fragment' => ['#image'],
            'protocol relative url' => ['//example.com/image.png'],
        ];
    }

    #[DataProvider('unsafeImageUrls')]
    public function test_unsafe_image_urls_are_removed(string $url): void
    {
        $html = app(RichHtmlSanitizer::class)->sanitize('<img src="'.$url.'" alt="Image">');

        $this->assertStringContainsString('<img alt="Image">', $html);
        $this->assertStringNotContainsString('src=', $html);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function safeImageUrls(): array
    {
        return [
            'https' => ['https://example.com/image.png'],
            'http' => ['http://example.com/image.png'],
            'relative' => ['images/photo.png'],
            'root relative' => ['/images/photo.png'],
        ];
    }

    #[DataProvider('safeImageUrls')]
    public function test_safe_image_urls_are_retained(string $url): void
    {
        $html = app(RichHtmlSanitizer::class)->sanitize('<img src="'.$url.'" alt="Image">');

        $this->assertStringContainsString('src="'.$url.'"', $html);
    }
}
