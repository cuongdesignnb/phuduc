# PR 2A Theme Token Specification

## Ownership and data flow

`ThemeTokenService` is the only component allowed to validate the primary color, derive the brand palette, choose accessible brand text, validate fonts, and produce storefront CSS variables.

```text
settings -> SiteConfigurationService -> ThemeTokenService
         -> initial Blade variables
         -> Inertia site.theme
         -> useThemeRuntime (apply only, no derivation)
```

The default primary color is `#ffd400`. Only six-digit HEX input is accepted. Invalid input falls back to the default. CSS variable names are application-owned and cannot be provided by users.

## Color derivation

- Convert the validated primary color to RGB.
- Derive hover and active colors by deterministic mixing with dark content.
- Derive soft, muted, and border colors by deterministic mixing with white.
- Calculate WCAG relative luminance and contrast against white and dark candidates.
- Choose the higher-contrast candidate for `--ds-brand-contrast` and require at least WCAG AA for normal button text.
- Derive the focus ring from the canonical brand RGB value.

## Canonical variables

### Brand

`--ds-brand-primary`, `--ds-brand-hover`, `--ds-brand-active`, `--ds-brand-soft`, `--ds-brand-muted`, `--ds-brand-border`, `--ds-brand-contrast`, `--ds-focus-ring`

### Surface

`--ds-surface-page`, `--ds-surface-card`, `--ds-surface-muted`, `--ds-surface-elevated`, `--ds-surface-inverse`

### Content

`--ds-content-primary`, `--ds-content-secondary`, `--ds-content-muted`, `--ds-content-inverse`

### Border and semantic states

`--ds-border-default`, `--ds-border-strong`, `--ds-border-subtle`, `--ds-success`, `--ds-success-soft`, `--ds-warning`, `--ds-warning-soft`, `--ds-danger`, `--ds-danger-soft`, `--ds-info`, `--ds-info-soft`

### Layout

`--ds-shell-max`, `--ds-content-max`, `--ds-page-gutter`, `--ds-header-height`

The shell container is approximately 1600px; the content container is approximately 1280px. Gutters resolve to 16px mobile, 24px tablet, 32px desktop, and 40px large desktop.

### Shape, elevation, and motion

`--ds-radius-sm`, `--ds-radius-md`, `--ds-radius-lg`, `--ds-radius-xl`, `--ds-shadow-sm`, `--ds-shadow-card`, `--ds-shadow-card-hover`, `--ds-shadow-dropdown`, `--motion-fast`, `--motion-base`, `--motion-slow`, `--motion-ease-standard`, `--motion-ease-emphasized`

### Typography

`--font-display` and `--font-sans` contain validated font-family stacks. The approved list is shared with the Admin settings list. Unknown or unsafe names fall back to `Rajdhani` for headings and `Inter` for body copy, each followed by a system sans-serif stack.

## Tailwind mapping

- Public canonical namespaces: `brand`, `surface`, `content`, `line`.
- Semantic state namespaces: `success`, `warning`, `danger`, `info` as needed.
- `volt` remains a deprecated Admin compatibility alias backed by canonical tokens.
- `carbon` remains an Admin-only compatibility palette.
- New public storefront code must not use `volt`, `carbon`, or `industrial`.

## Bootstrap and runtime invariants

- The root Blade response contains `<style id="storefront-theme">` before the application mounts.
- Blade renders a pre-normalized map and performs no color calculations.
- Initial Google Font links use validated names only.
- `app.css` contains no fixed Google Font import.
- `useThemeRuntime` watches `site.theme`, applies only whitelisted canonical variables, and avoids no-op reapplication.
- The runtime creates no duplicate style elements and derives no colors.
- Root initialization occurs once; no theme/font global mixin is allowed.
