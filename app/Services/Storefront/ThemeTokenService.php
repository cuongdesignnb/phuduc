<?php

namespace App\Services\Storefront;

class ThemeTokenService
{
    public const DEFAULT_PRIMARY_COLOR = '#ffd400';

    public const DEFAULT_HEADING_FONT = 'Rajdhani';

    public const DEFAULT_BODY_FONT = 'Inter';

    /**
     * This is the canonical font allowlist shared by Admin and the storefront.
     *
     * @var array<string, string>
     */
    public const FONT_OPTIONS = [
        'Inter' => 'sans-serif',
        'Be Vietnam Pro' => 'sans-serif',
        'Nunito' => 'sans-serif',
        'Nunito Sans' => 'sans-serif',
        'Montserrat' => 'sans-serif',
        'Open Sans' => 'sans-serif',
        'Roboto' => 'sans-serif',
        'Source Sans 3' => 'sans-serif',
        'Mulish' => 'sans-serif',
        'Quicksand' => 'sans-serif',
        'Lexend' => 'sans-serif',
        'Rajdhani' => 'sans-serif',
        'Barlow' => 'sans-serif',
        'Barlow Condensed' => 'sans-serif',
        'Josefin Sans' => 'sans-serif',
        'Space Grotesk' => 'sans-serif',
        'Exo 2' => 'sans-serif',
        'Sarabun' => 'sans-serif',
        'Noto Sans' => 'sans-serif',
        'Lora' => 'serif',
        'Merriweather' => 'serif',
        'Playfair Display' => 'serif',
        'EB Garamond' => 'serif',
    ];

    /**
     * @return array{
     *     primary_color: string,
     *     fonts: array{heading: string, body: string},
     *     css_variables: array<string, string>,
     *     font_stylesheet_url: string
     * }
     */
    public function resolve(?string $primaryColor, ?string $headingFont, ?string $bodyFont): array
    {
        $primaryColor = $this->normalizeHex($primaryColor);
        $headingFont = $this->normalizeFont($headingFont, self::DEFAULT_HEADING_FONT);
        $bodyFont = $this->normalizeFont($bodyFont, self::DEFAULT_BODY_FONT);

        $primary = $this->hexToRgb($primaryColor);
        $white = [255, 255, 255];
        $dark = [17, 24, 39];
        $contrast = $this->contrastRatio($primary, $white) >= $this->contrastRatio($primary, $dark)
            ? $white
            : $dark;

        // White and the dark candidate normally guarantee AA. Pure black is a
        // deterministic safety net for colors close to the WCAG crossover.
        if ($this->contrastRatio($primary, $contrast) < 4.5) {
            $contrast = [0, 0, 0];
        }

        // Prefer a darker interaction state, then fall back to a lighter one
        // when needed so every state is distinct and retains AA contrast.
        $hover = $this->interactionColor($primary, $contrast, $dark, $white, 0.08);
        $active = $this->interactionColor($primary, $contrast, $dark, $white, 0.14);

        $variables = [
            '--ds-brand-primary' => $this->rgbValue($primary),
            '--ds-brand-hover' => $this->rgbValue($hover),
            '--ds-brand-active' => $this->rgbValue($active),
            '--ds-brand-soft' => $this->rgbValue($this->mix($primary, $white, 0.90)),
            '--ds-brand-muted' => $this->rgbValue($this->mix($primary, $white, 0.78)),
            '--ds-brand-border' => $this->rgbValue($this->mix($primary, $white, 0.58)),
            '--ds-brand-contrast' => $this->rgbValue($contrast),
            '--ds-brand-text' => $this->rgbValue($this->accessibleBrandText($primary, $white, $dark)),
            '--ds-focus-ring' => $this->rgbValue($primary),
            '--ds-surface-page' => '246 247 249',
            '--ds-surface-card' => '255 255 255',
            '--ds-surface-muted' => '241 244 247',
            '--ds-surface-elevated' => '255 255 255',
            '--ds-surface-inverse' => '17 24 39',
            '--ds-content-primary' => '24 31 42',
            '--ds-content-secondary' => '71 81 95',
            '--ds-content-muted' => '91 103 119',
            '--ds-content-inverse' => '255 255 255',
            '--ds-border-default' => '218 223 230',
            '--ds-border-strong' => '183 191 202',
            '--ds-border-subtle' => '235 238 242',
            '--ds-success' => '5 150 105',
            '--ds-success-soft' => '209 250 229',
            '--ds-warning' => '217 119 6',
            '--ds-warning-soft' => '254 243 199',
            '--ds-danger' => '220 38 38',
            '--ds-danger-soft' => '254 226 226',
            '--ds-info' => '37 99 235',
            '--ds-info-soft' => '219 234 254',
            '--ds-shell-max' => '100rem',
            '--ds-content-max' => '80rem',
            '--ds-page-gutter' => 'clamp(1rem, 2.5vw, 2.5rem)',
            '--ds-header-height' => '4.5rem',
            '--ds-radius-sm' => '0.375rem',
            '--ds-radius-md' => '0.625rem',
            '--ds-radius-lg' => '0.875rem',
            '--ds-radius-xl' => '1.25rem',
            '--ds-shadow-sm' => '0 1px 2px rgb(15 23 42 / 0.06)',
            '--ds-shadow-card' => '0 8px 24px rgb(15 23 42 / 0.07)',
            '--ds-shadow-card-hover' => '0 14px 36px rgb(15 23 42 / 0.12)',
            '--ds-shadow-dropdown' => '0 18px 50px rgb(15 23 42 / 0.16)',
            '--motion-fast' => '120ms',
            '--motion-base' => '200ms',
            '--motion-slow' => '320ms',
            '--motion-ease-standard' => 'cubic-bezier(0.2, 0, 0, 1)',
            '--motion-ease-emphasized' => 'cubic-bezier(0.2, 0.8, 0.2, 1)',
            '--font-display' => $this->fontStack($headingFont),
            '--font-sans' => $this->fontStack($bodyFont),
        ];

        return [
            'primary_color' => $primaryColor,
            'fonts' => [
                'heading' => $headingFont,
                'body' => $bodyFont,
            ],
            'css_variables' => $variables,
            'font_stylesheet_url' => $this->fontStylesheetUrl([$headingFont, $bodyFont]),
        ];
    }

    /**
     * @return list<array{name: string, category: string}>
     */
    public static function fontOptions(): array
    {
        return collect(self::FONT_OPTIONS)
            ->map(fn (string $category, string $name): array => [
                'name' => $name,
                'category' => $category,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function cssVariableNames(): array
    {
        return array_keys($this->resolve(null, null, null)['css_variables']);
    }

    private function normalizeHex(?string $color): string
    {
        if (! is_string($color) || preg_match('/^#[0-9a-fA-F]{6}$/', $color) !== 1) {
            return self::DEFAULT_PRIMARY_COLOR;
        }

        return strtolower($color);
    }

    private function normalizeFont(?string $font, string $fallback): string
    {
        return is_string($font) && array_key_exists($font, self::FONT_OPTIONS)
            ? $font
            : $fallback;
    }

    /**
     * @return array{int, int, int}
     */
    private function hexToRgb(string $color): array
    {
        return [
            hexdec(substr($color, 1, 2)),
            hexdec(substr($color, 3, 2)),
            hexdec(substr($color, 5, 2)),
        ];
    }

    /**
     * @param  array{int, int, int}  $from
     * @param  array{int, int, int}  $to
     * @return array{int, int, int}
     */
    private function mix(array $from, array $to, float $weight): array
    {
        return [
            (int) round($from[0] + (($to[0] - $from[0]) * $weight)),
            (int) round($from[1] + (($to[1] - $from[1]) * $weight)),
            (int) round($from[2] + (($to[2] - $from[2]) * $weight)),
        ];
    }

    /**
     * @param  array{int, int, int}  $rgb
     */
    private function relativeLuminance(array $rgb): float
    {
        $channels = array_map(function (int $channel): float {
            $value = $channel / 255;

            return $value <= 0.04045
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);
    }

    /**
     * @param  array{int, int, int}  $first
     * @param  array{int, int, int}  $second
     */
    private function contrastRatio(array $first, array $second): float
    {
        $lighter = max($this->relativeLuminance($first), $this->relativeLuminance($second));
        $darker = min($this->relativeLuminance($first), $this->relativeLuminance($second));

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * @param  array{int, int, int}  $primary
     * @param  array{int, int, int}  $surface
     * @param  array{int, int, int}  $dark
     * @return array{int, int, int}
     */
    private function accessibleBrandText(array $primary, array $surface, array $dark): array
    {
        if ($this->contrastRatio($primary, $surface) >= 4.5) {
            return $primary;
        }

        for ($step = 1; $step <= 20; $step++) {
            $candidate = $this->mix($primary, $dark, $step * 0.05);

            if ($this->contrastRatio($candidate, $surface) >= 4.5) {
                return $candidate;
            }
        }

        return $dark;
    }

    /**
     * @param  array{int, int, int}  $primary
     * @param  array{int, int, int}  $contrast
     * @param  array{int, int, int}  $preferredTarget
     * @param  array{int, int, int}  $fallbackTarget
     * @return array{int, int, int}
     */
    private function interactionColor(
        array $primary,
        array $contrast,
        array $preferredTarget,
        array $fallbackTarget,
        float $weight,
    ): array {
        foreach ([$preferredTarget, $fallbackTarget] as $target) {
            $candidate = $this->mix($primary, $target, $weight);

            if ($candidate !== $primary && $this->contrastRatio($candidate, $contrast) >= 4.5) {
                return $candidate;
            }
        }

        return $primary;
    }

    /**
     * @param  array{int, int, int}  $rgb
     */
    private function rgbValue(array $rgb): string
    {
        return implode(' ', $rgb);
    }

    private function fontStack(string $font): string
    {
        $generic = self::FONT_OPTIONS[$font] ?? 'sans-serif';

        return '"'.$font.'", ui-sans-serif, system-ui, '.$generic;
    }

    /**
     * @param  list<string>  $fonts
     */
    private function fontStylesheetUrl(array $fonts): string
    {
        $families = collect($fonts)
            ->unique()
            ->map(fn (string $font): string => 'family='.rawurlencode($font).':wght@300;400;500;600;700;800;900')
            ->implode('&');

        return 'https://fonts.googleapis.com/css2?'.$families.'&display=swap';
    }
}
