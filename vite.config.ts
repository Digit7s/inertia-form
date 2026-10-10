import vue from "@vitejs/plugin-vue";
import { defineConfig } from "vitest/config";

export default defineConfig({
    plugins: [vue()],
    build: {
        lib: {
            entry: "resources/js/components/InertiaForm.vue",
            formats: ["es"],
            fileName: "inertia-form",
        },
        rollupOptions: {
            external: ["vue", "@inertiajs/vue3"],
        },
    },
    test: {
        environment: "jsdom",
        include: ["tests/Frontend/**/*.test.ts"],
    },
});
