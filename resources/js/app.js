import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useFontLoader } from './Composables/useFontLoader';
import { useColorLoader } from './Composables/useColorLoader';
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
            render() {
                return h('div', [h(App, props), h(ToastNotification)]);
            },
        })
            .use(plugin)
            .use(ZiggyVue);

        // Global mixin for font and color loading on every page
        app.mixin({
            setup() {
                useFontLoader();
                useColorLoader();
            },
        });

        const vueApp = app.mount(el);

        // Listen to Inertia navigate events to show flash toasts
        const toast = useToast();
        router.on('navigate', (event) => {
            const flash = event.detail.page.props?.flash;
            if (flash?.success) toast.success(flash.success);
            if (flash?.error) toast.error(flash.error);
        });

        return vueApp;
    },
    progress: {
        color: '#09DE52',
    },
});
