import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import path from 'path';

/**
 * Configuración de Vite para Laravel + Vue 3 + Inertia.js (SOGAC).
 * Propósito: Compilación de assets y servidor de desarrollo HMR compatible con navegadores en localhost/127.0.0.1.
 * Historial:
 *  - 2026-11-16: Se cambia `server.host` de `0.0.0.0` a `127.0.0.1`, agregando `cors: true` y `hmr.host: '127.0.0.1'`.
 *    El host anterior (`0.0.0.0`) escribía `http://0.0.0.0:5173` en `public/hot`, provocando errores de carga
 *    (ERR_ADDRESS_INVALID / bloqueo del navegador) y pantalla en blanco al abrir `http://127.0.0.1:8000`.
 */
export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            'ziggy-js': path.resolve('vendor/tightenco/ziggy'),
        }
    },
    server: {
        host: '127.0.0.1',
        port: 5173,
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
});

