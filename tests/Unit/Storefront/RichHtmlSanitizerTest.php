<?php

namespace Tests\Unit\Storefront;

use App\Services\Storefront\RichHtmlSanitizer;
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
}
