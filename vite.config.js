import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import svgLoader from 'vite-svg-loader'
import path from "path"

export default defineConfig({
    server: {
        hmr: {
            host: 'localhost',
        },
    },
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
        svgLoader(),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
        }
    },
    build: {
        rollupOptions: {
            manualChunks: (path) => {
                if (path.includes('vue-quill')) {
                    return 'quill';
                }

                if (path.includes('vendor')) {
                    return 'vendor';
                }

                return "all"
            }
        }
    }
});
