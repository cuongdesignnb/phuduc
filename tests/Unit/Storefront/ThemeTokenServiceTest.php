<?php

namespace Tests\Unit\Storefront;

use App\Services\Storefront\ThemeTokenService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ThemeTokenServiceTest extends TestCase
{
    #[DataProvider('primaryColors')]
    public function test_it_normalizes_colors_and_generates_an_aa_contrast_token(string $input, string $expected): void
    {
        $theme = (new ThemeTokenService)->resolve($input, 'Rajdhani', 'Inter');

        $this->assertSame($expected, $theme['primary_color']);
        $this->assertGreaterThanOrEqual(
            4.5,
            $this->contrastRatio(
                $this->rgb($theme['css_variables']['--ds-brand-primary']),
                $this->rgb($theme['css_variables']['--ds-brand-contrast']),
            ),
        );

        foreach (['--ds-brand-primary', '--ds-brand-hover', '--ds-brand-active'] as $background) {
            $this->assertGreaterThanOrEqual(
                4.5,
                $this->contrastRatio(
                    $this->rgb($theme['css_variables'][$background]),
                    $this->rgb($theme['css_variables']['--ds-brand-contrast']),
                ),
                "$background must retain AA contrast.",
            );
        }

        $this->assertNotSame(
            $theme['css_variables']['--ds-brand-primary'],
            $theme['css_variables']['--ds-brand-hover'],
        );
        $this->assertNotSame(
            $theme['css_variables']['--ds-brand-primary'],
            $theme['css_variables']['--ds-brand-active'],
        );

        $this->assertGreaterThanOrEqual(
            4.5,
            $this->contrastRatio(
                $this->rgb($theme['css_variables']['--ds-brand-text']),
                [255, 255, 255],
            ),
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function primaryColors(): array
    {
        return [
            'default yellow' => ['#ffd400', '#ffd400'],
            'dark slate' => ['#111827', '#111827'],
            'white' => ['#ffffff', '#ffffff'],
            'black' => ['#000000', '#000000'],
            'blue' => ['#2563eb', '#2563eb'],
            'red' => ['#ef4444', '#ef4444'],
            'invalid input' => ['not-a-color', ThemeTokenService::DEFAULT_PRIMARY_COLOR],
        ];
    }

    public function test_derivation_is_deterministic_and_variable_names_are_application_owned(): void
    {
        $service = new ThemeTokenService;
        $first = $service->resolve('#2563eb', 'Inter', 'Lora');
        $second = $service->resolve('#2563eb', 'Inter', 'Lora');

        $this->assertSame($first, $second);
        $this->assertSame($service->cssVariableNames(), array_keys($first['css_variables']));
        $this->assertContains('--ds-brand-primary', $service->cssVariableNames());
        $this->assertContains('--motion-ease-standard', $service->cssVariableNames());
        $this->assertNotContains('--user-controlled', $service->cssVariableNames());
    }

    public function test_fonts_are_whitelisted_and_unsafe_values_fall_back(): void
    {
        $theme = (new ThemeTokenService)->resolve(
            '#2563eb',
            '";url(https://example.test/font)',
            '<script>alert(1)</script>',
        );

        $this->assertSame(ThemeTokenService::DEFAULT_HEADING_FONT, $theme['fonts']['heading']);
        $this->assertSame(ThemeTokenService::DEFAULT_BODY_FONT, $theme['fonts']['body']);
        $this->assertStringNotContainsString('example.test', $theme['font_stylesheet_url']);
        $this->assertStringNotContainsString('<script', $theme['font_stylesheet_url']);
        $this->assertStringStartsWith('https://fonts.googleapis.com/css2?', $theme['font_stylesheet_url']);
    }

    #[DataProvider('focusRingColors')]
    public function test_focus_ring_contrasts_with_page_and_card_surfaces(string $color): void
    {
        $theme = (new ThemeTokenService)->resolve($color, 'Rajdhani', 'Inter');
        $focusRing = $this->rgb($theme['css_variables']['--ds-focus-ring']);
        $surfacePage = [246, 247, 249];
        $surfaceCard = [255, 255, 255];

        $this->assertGreaterThanOrEqual(
            3.0,
            $this->contrastRatio($focusRing, $surfacePage),
            "Focus ring must have ≥ 3:1 contrast against surface-page for $color.",
        );
        $this->assertGreaterThanOrEqual(
            3.0,
            $this->contrastRatio($focusRing, $surfaceCard),
            "Focus ring must have ≥ 3:1 contrast against surface-card for $color.",
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function focusRingColors(): array
    {
        return [
            'white' => ['#ffffff'],
            'near-white' => ['#fefefe'],
            'pastel yellow' => ['#fffbea'],
            'default yellow' => ['#ffd400'],
            'blue' => ['#2563eb'],
            'dark slate' => ['#111827'],
            'black' => ['#000000'],
        ];
    }

    public function test_white_primary_has_visible_focus_ring(): void
    {
        $theme = (new ThemeTokenService)->resolve('#ffffff', 'Rajdhani', 'Inter');
        $focusRing = $this->rgb($theme['css_variables']['--ds-focus-ring']);

        // Focus ring must NOT be white/near-white on white surfaces.
        $this->assertNotSame('255 255 255', $theme['css_variables']['--ds-focus-ring']);
        $this->assertGreaterThanOrEqual(
            3.0,
            $this->contrastRatio($focusRing, [255, 255, 255]),
        );
    }

    public function test_near_white_primary_has_visible_focus_ring(): void
    {
        $theme = (new ThemeTokenService)->resolve('#fefefe', 'Rajdhani', 'Inter');

        $this->assertNotSame('254 254 254', $theme['css_variables']['--ds-focus-ring']);
        $this->assertGreaterThanOrEqual(
            3.0,
            $this->contrastRatio(
                $this->rgb($theme['css_variables']['--ds-focus-ring']),
                [255, 255, 255],
            ),
        );
    }

    public function test_yellow_primary_has_visible_focus_ring(): void
    {
        $theme = (new ThemeTokenService)->resolve('#ffd400', 'Rajdhani', 'Inter');

        $this->assertGreaterThanOrEqual(
            3.0,
            $this->contrastRatio(
                $this->rgb($theme['css_variables']['--ds-focus-ring']),
                [246, 247, 249],
            ),
        );
    }

    public function test_white_primary_has_visible_control_border(): void
    {
        $theme = (new ThemeTokenService)->resolve('#ffffff', 'Rajdhani', 'Inter');
        $controlBorder = $this->rgb($theme['css_variables']['--ds-brand-control-border']);

        $this->assertArrayHasKey('--ds-brand-control-border', $theme['css_variables']);
        $this->assertGreaterThanOrEqual(
            3.0,
            $this->contrastRatio($controlBorder, [255, 255, 255]),
            'Control border must be visible against surface-card for white primary.',
        );
    }

    #[DataProvider('focusRingColors')]
    public function test_hover_and_active_are_distinct(string $color): void
    {
        $theme = (new ThemeTokenService)->resolve($color, 'Rajdhani', 'Inter');

        $this->assertNotSame(
            $theme['css_variables']['--ds-brand-primary'],
            $theme['css_variables']['--ds-brand-hover'],
            "Hover must differ from primary for $color.",
        );
        $this->assertNotSame(
            $theme['css_variables']['--ds-brand-primary'],
            $theme['css_variables']['--ds-brand-active'],
            "Active must differ from primary for $color.",
        );
        $this->assertNotSame(
            $theme['css_variables']['--ds-brand-hover'],
            $theme['css_variables']['--ds-brand-active'],
            "Hover and active must differ from each other for $color.",
        );
    }

    #[DataProvider('focusRingColors')]
    public function test_brand_soft_and_muted_are_distinct_from_surface(string $color): void
    {
        $theme = (new ThemeTokenService)->resolve($color, 'Rajdhani', 'Inter');
        $surfaceCard = [255, 255, 255];

        $soft = $this->rgb($theme['css_variables']['--ds-brand-soft']);
        $muted = $this->rgb($theme['css_variables']['--ds-brand-muted']);

        // Soft and muted must not be identical to the card surface.
        $this->assertNotSame('255 255 255', $theme['css_variables']['--ds-brand-soft'],
            "Brand-soft must not be pure white for $color.",
        );
        $this->assertNotSame('255 255 255', $theme['css_variables']['--ds-brand-muted'],
            "Brand-muted must not be pure white for $color.",
        );
    }

    /**
     * @return array{float, float, float}
     */
    private function rgb(string $value): array
    {
        return array_map('floatval', explode(' ', $value));
    }

    /**
     * @param  array{float, float, float}  $first
     * @param  array{float, float, float}  $second
     */
    private function contrastRatio(array $first, array $second): float
    {
        $firstLuminance = $this->luminance($first);
        $secondLuminance = $this->luminance($second);

        return (max($firstLuminance, $secondLuminance) + 0.05)
            / (min($firstLuminance, $secondLuminance) + 0.05);
    }

    /**
     * @param  array{float, float, float}  $rgb
     */
    private function luminance(array $rgb): float
    {
        $channels = array_map(function (float $channel): float {
            $value = $channel / 255;

            return $value <= 0.04045
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
    }
}
