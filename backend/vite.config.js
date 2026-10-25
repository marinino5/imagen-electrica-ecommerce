import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // Títulos condensados en negrita y texto en sans-serif, como el catálogo
                bunny('Barlow Condensed', {
                    weights: [600, 700],
                }),
                bunny('Barlow', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 5 todavía usa @import y funciones de color antiguas de Sass;
                // se ocultan esos avisos para que la salida del build sea legible.
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
