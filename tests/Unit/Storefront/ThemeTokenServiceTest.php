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
