import { usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';

const allowedVariables = new Set([
    '--ds-brand-primary',
    '--ds-brand-hover',
    '--ds-brand-active',
    '--ds-brand-soft',
    '--ds-brand-muted',
    '--ds-brand-border',
    '--ds-brand-contrast',
    '--ds-brand-text',
    '--ds-focus-ring',
    '--ds-surface-page',
    '--ds-surface-card',
    '--ds-surface-muted',
    '--ds-surface-elevated',
    '--ds-surface-inverse',
    '--ds-content-primary',
    '--ds-content-secondary',
    '--ds-content-muted',
    '--ds-content-inverse',
    '--ds-border-default',
    '--ds-border-strong',
    '--ds-border-subtle',
    '--ds-success',
    '--ds-success-soft',
    '--ds-warning',
    '--ds-warning-soft',
    '--ds-danger',
    '--ds-danger-soft',
    '--ds-info',
    '--ds-info-soft',
    '--ds-shell-max',
    '--ds-content-max',
    '--ds-page-gutter',
    '--ds-header-height',
    '--ds-radius-sm',
    '--ds-radius-md',
    '--ds-radius-lg',
    '--ds-radius-xl',
    '--ds-shadow-sm',
    '--ds-shadow-card',
    '--ds-shadow-card-hover',
    '--ds-shadow-dropdown',
    '--motion-fast',
    '--motion-base',
    '--motion-slow',
    '--motion-ease-standard',
    '--motion-ease-emphasized',
    '--font-display',
    '--font-sans',
]);

let lastSignature = '';

function applyFontStylesheet(url) {
    if (!url?.startsWith('https://fonts.googleapis.com/css2?')) return;

    const link = document.getElementById('storefront-theme-fonts');
    if (link && link.getAttribute('href') !== url) {
        link.setAttribute('href', url);
    }
}

function applyTheme(theme) {
    const variables = theme?.css_variables || {};
    const entries = Object.entries(variables)
        .filter(([name, value]) => allowedVariables.has(name) && typeof value === 'string')
        .sort(([left], [right]) => left.localeCompare(right));
    const signature = JSON.stringify([entries, theme?.font_stylesheet_url || '']);

    if (!entries.length || signature === lastSignature) return;

    const root = document.documentElement;
    entries.forEach(([name, value]) => root.style.setProperty(name, value));
    applyFontStylesheet(theme.font_stylesheet_url);
    lastSignature = signature;
}

export function useThemeRuntime() {
    const page = usePage();

    onMounted(() => applyTheme(page.props?.site?.theme));
    watch(
        () => page.props?.site?.theme,
        (theme) => applyTheme(theme),
        { deep: true },
    );
}
