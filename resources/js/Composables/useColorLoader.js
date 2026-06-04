import { usePage } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';

function hexToHsl(hex) {
    let r = parseInt(hex.slice(1, 3), 16) / 255;
    let g = parseInt(hex.slice(3, 5), 16) / 255;
    let b = parseInt(hex.slice(5, 7), 16) / 255;

    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    let h, s, l = (max + min) / 2;

    if (max === min) {
        h = s = 0;
    } else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        switch (max) {
            case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
            case g: h = ((b - r) / d + 2) / 6; break;
            case b: h = ((r - g) / d + 4) / 6; break;
        }
    }

    return [h * 360, s * 100, l * 100];
}

function hslToRgb(h, s, l) {
    h /= 360; s /= 100; l /= 100;
    let r, g, b;

    if (s === 0) {
        r = g = b = l;
    } else {
        const hue2rgb = (p, q, t) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1 / 6) return p + (q - p) * 6 * t;
            if (t < 1 / 2) return q;
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
            return p;
        };
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        r = hue2rgb(p, q, h + 1 / 3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1 / 3);
    }

    return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
}

function generatePalette(hex) {
    const [h, s] = hexToHsl(hex);

    const shades = {
        50:  [h, Math.min(s * 0.35, 100), 96],
        100: [h, Math.min(s * 0.45, 100), 91],
        200: [h, Math.min(s * 0.55, 100), 83],
        300: [h, Math.min(s * 0.65, 100), 72],
        400: [h, Math.min(s * 0.85, 100), 58],
        500: [h, s, 45],
        600: [h, Math.min(s * 1.05, 100), 37],
        700: [h, Math.min(s * 0.95, 100), 30],
        800: [h, Math.min(s * 0.85, 100), 24],
        900: [h, Math.min(s * 0.75, 100), 20],
        950: [h, Math.min(s * 0.7, 100), 10],
    };

    const palette = {};
    for (const [shade, [sh, ss, sl]] of Object.entries(shades)) {
        const [r, g, b] = hslToRgb(sh, ss, sl);
        palette[shade] = `${r} ${g} ${b}`;
    }
    return palette;
}

function applyPrimaryColor(hex) {
    if (!hex || !/^#[0-9A-Fa-f]{6}$/.test(hex)) return;

    const root = document.documentElement;
    const palette = generatePalette(hex);

    for (const [shade, rgb] of Object.entries(palette)) {
        root.style.setProperty(`--volt-${shade}`, rgb);
    }
}

export function useColorLoader() {
    const page = usePage();

    const init = () => {
        const color = page.props.primaryColor;
        if (color) applyPrimaryColor(color);
    };

    onMounted(init);

    watch(() => page.props.primaryColor, (color) => {
        if (color) applyPrimaryColor(color);
    });
}
