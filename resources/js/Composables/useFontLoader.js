import { usePage } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';

const loadedFonts = new Set();

function loadGoogleFont(fontName) {
    if (!fontName || loadedFonts.has(fontName)) return;
    loadedFonts.add(fontName);

    const encoded = fontName.replace(/ /g, '+');
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = `https://fonts.googleapis.com/css2?family=${encoded}:wght@300;400;500;600;700;800;900&display=swap`;
    document.head.appendChild(link);
}

function applyFonts(heading, body) {
    const root = document.documentElement;
    if (heading) {
        loadGoogleFont(heading);
        root.style.setProperty('--font-display', `'${heading}', sans-serif`);
    }
    if (body) {
        loadGoogleFont(body);
        root.style.setProperty('--font-sans', `'${body}', sans-serif`);
    }
}

export function useFontLoader() {
    const page = usePage();

    const init = () => {
        const fs = page.props.fontSettings;
        if (fs) {
            applyFonts(fs.heading, fs.body);
        }
    };

    onMounted(init);

    watch(() => page.props.fontSettings, (fs) => {
        if (fs) applyFonts(fs.heading, fs.body);
    }, { deep: true });
}
