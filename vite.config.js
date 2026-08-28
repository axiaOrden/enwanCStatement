import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { normalizeBasePath, viteBuildBase } from './resources/js/base-path.js';

export default defineConfig(({ command, mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const appBasePath = normalizeBasePath(
        process.env.VITE_APP_BASE_PATH ?? env.VITE_APP_BASE_PATH,
    );

    return {
        base: command === 'build' && appBasePath !== '/'
            ? viteBuildBase(appBasePath)
            : undefined,
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
    };
});
