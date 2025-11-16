import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { createVuePlugin } from 'vite-plugin-vue2';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        createVuePlugin(),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm.js',
        },
    },
    define: {
        'process.env': {
            MIX_PUSHER_APP_KEY: JSON.stringify(process.env.PUSHER_APP_KEY || ''),
            MIX_PUSHER_APP_CLUSTER: JSON.stringify(process.env.PUSHER_APP_CLUSTER || 'mt1'),
        },
    },
});
