import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useThemeRuntime } from './Composables/useThemeRuntime';
import { useToast } from './Composables/useToast';
import ToastNotification from './Components/ToastNotification.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({
            setup() {
                useThemeRuntime();
            },
            render() {
                return h('div', [h(App, props), h(ToastNotification)]);
            },
        })
            .use(plugin)
            .use(ZiggyVue);

        // Register global helper to fix broken encoding from database
        app.config.globalProperties.$fixText = (value) => {
            if (typeof value !== 'string') return value;
            const codes = Array.from(value).map((char) => char.charCodeAt(0));
            const isBroken = codes.some((code) => [0xc2, 0xc3, 0xc4, 0xc6, 0xca, 0xfffd].includes(code))
                || codes.some((code) => code >= 0x80 && code <= 0x9f)
                || codes.some((code, index) => code === 0xe1 && [0xba, 0xbb].includes(codes[index + 1]));
            if (!isBroken) return value;
            try {
                const bytes = Uint8Array.from(Array.from(value), (char) => char.charCodeAt(0) & 255);
                return new TextDecoder('utf-8', { fatal: false }).decode(bytes);
            } catch {
                return value;
            }
        };

        const vueApp = app.mount(el);

        // Listen to Inertia navigate events to show flash toasts
        const toast = useToast();
        router.on('navigate', (event) => {
            const flash = event.detail.page.props?.flash;
            if (flash?.success) toast.success(app.config.globalProperties.$fixText(flash.success));
            if (flash?.error) toast.error(app.config.globalProperties.$fixText(flash.error));
        });

        return vueApp;
    },
    progress: {
        color: '#475569',
    },
});
