<?php

namespace Tests\Feature\Storefront;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_response_contains_server_rendered_canonical_tokens_before_the_app(): void
    {
        Setting::set('site.primary_color', '#2563eb', 'color');

        $response = $this->get('/')->assertOk();
        $content = $response->getContent();

        $response->assertSee('id="storefront-theme"', false)
            ->assertSee('--ds-brand-primary: 37 99 235;', false)
            ->assertSee('--ds-brand-contrast:', false)
            ->assertDontSee('--user-controlled:', false);

        $appScriptPosition = strpos($content, '<script type="module"');
        $this->assertNotFalse($appScriptPosition);
        $this->assertLessThan($appScriptPosition, strpos($content, 'id="storefront-theme"'));
        $this->assertStringNotContainsString('--volt-', $content);
    }

    public function test_root_response_uses_whitelisted_font_fallbacks(): void
    {
        Setting::set('font.heading', '";url(https://example.test/font)', 'font');
        Setting::set('font.body', '<script>alert(1)</script>', 'font');

        $this->get('/')
            ->assertOk()
            ->assertSee('family=Rajdhani', false)
            ->assertSee('family=Inter', false)
            ->assertDontSee('example.test', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
