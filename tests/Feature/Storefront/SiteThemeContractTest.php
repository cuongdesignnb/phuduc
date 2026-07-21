<?php

namespace Tests\Feature\Storefront;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SiteThemeContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_contract_exposes_the_canonical_theme_and_legacy_compatibility_values(): void
    {
        Setting::set('site.primary_color', '#111827', 'color');
        Setting::set('font.heading', 'Barlow Condensed', 'font');
        Setting::set('font.body', 'Be Vietnam Pro', 'font');

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('site.theme.primary_color', '#111827')
            ->where('site.theme.fonts.heading', 'Barlow Condensed')
            ->where('site.theme.fonts.body', 'Be Vietnam Pro')
            ->where('site.theme.css_variables.--ds-brand-primary', '17 24 39')
            ->has('site.theme.css_variables.--ds-brand-contrast')
            ->has('site.theme.font_stylesheet_url')
            ->where('site.primary_color', '#111827')
            ->where('site.fonts.heading', 'Barlow Condensed')
        );
    }

    public function test_invalid_settings_cannot_add_css_variables_or_unsafe_fonts(): void
    {
        Setting::set('site.primary_color', 'var(--attack)', 'color');
        Setting::set('font.heading', '";url(https://example.test)', 'font');

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('site.theme.primary_color', '#ffd400')
            ->where('site.theme.fonts.heading', 'Rajdhani')
            ->missing('site.theme.css_variables.--attack')
            ->where('site.theme.css_variables.--ds-brand-primary', '255 212 0')
        );
    }
}
