import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

export default defineConfig({
    plugins: [
        symfonyPlugin(),
    ],
    build: {
        rollupOptions: {
            input: {
                // FRONT
                js: "./templates/js/app.js",
                style: "./templates/style/style.scss",

                // ADMIN
                adminJs: "./templates/js/app-admin.js",
                adminStyle: "./templates/style/admin-style.scss",
            },
        },
    }
});
