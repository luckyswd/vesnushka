import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import { viteStaticCopy } from 'vite-plugin-static-copy';
import path from 'path';

export default defineConfig({
    plugins: [
        symfonyPlugin(),
        viteStaticCopy({
            targets: [
                {
                    src: "./templates/common/fonts/**/*",
                    dest: "assets/fonts",
                },
            ],
        }),
    ],
    resolve: {
        alias: {
            '@admin': path.resolve(__dirname, 'templates/admin/assets'),
            '@common': path.resolve(__dirname, 'templates/common'),
            '@front': path.resolve(__dirname, 'templates/front/assets'),
        },
    },
    build: {
        rollupOptions: {
            input: {
                // FRONT
                frontApp: "./templates/front/assets/app.js",
                frontStyle: "./templates/front/assets/app.css",

                // ADMIN
                adminApp: "./templates/admin/assets/app-admin.js",
                adminStyle: "./templates/admin/assets/app-admin.css",
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `
                @import "@common/style/global/_variables.scss";
                @import "@common/style/global/_mixins.scss";
                `,
            },
        },
    },
});
