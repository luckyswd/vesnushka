import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import { viteStaticCopy } from 'vite-plugin-static-copy'
import path from 'path';

export default defineConfig({
    plugins: [
        symfonyPlugin(),
        viteStaticCopy({
            targets: [
                {
                    src: "./templates/assets/fonts/**/*",
                    dest: "assets/fonts",
                }
            ],
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'templates/assets'),
        },
    },
    build: {
        rollupOptions: {
            input: {
                app: "./templates/assets/app.js",
                css: "./templates/assets/app.css",
            },
        }
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `
                    @import "@/style/global/_variables.scss";
                    @import "@/style/global/_mixins.scss";
                `,
            },
        },
    },
});
