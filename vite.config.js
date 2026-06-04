import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const port = parseInt(process.env.VITE_PORT || '5173');

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: port,
        strictPort: true,
        hmr: {
            host: 'localhost',
            port: port,
        },
        watch: {
            usePolling: true,
            interval: 1000,
        },
    },
});
