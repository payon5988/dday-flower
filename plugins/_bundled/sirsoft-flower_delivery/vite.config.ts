import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        lib: {
            entry: resolve(__dirname, 'resources/js/index.ts'),
            name: 'SirsoftFlowerDelivery',
            formats: ['iife'],
            cssFileName: 'plugin',
        },
        outDir: 'dist',
        emptyOutDir: false, // 동봉 vendor 보존 — 산출물 정리는 빌드 커맨드가 한다
        rollupOptions: {
            output: {
                entryFileNames: 'js/plugin.iife.js',
                assetFileNames: (info) => {
                    if (info.name && info.name.endsWith('.css')) return 'css/plugin.css';
                    return 'assets/[name][extname]';
                },
            },
        },
        sourcemap: !['0', 'false'].includes(process.env.G7_BUILD_SOURCEMAP ?? ''),
        minify: 'esbuild',
        target: 'es2020',
        chunkSizeWarningLimit: 500,
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
});
