#!/usr/bin/env bash
set -euo pipefail

PACKAGE_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
HOST_ROOT="$(mktemp -d "${TMPDIR:-/tmp}/inertia-form-consumer.XXXXXX")"
cleanup_consumer_host() {
    if [[ "${KEEP_CONSUMER_HOST:-0}" == "1" ]]; then
        echo "Consumer host retained at: $HOST_ROOT"
    else
        rm -rf "$HOST_ROOT"
    fi
}
trap cleanup_consumer_host EXIT

composer create-project laravel/laravel:^13.0 "$HOST_ROOT" --no-interaction --prefer-dist

HOST_ROOT="$HOST_ROOT" PACKAGE_ROOT="$PACKAGE_ROOT" php <<'PHP'
<?php
$host = getenv('HOST_ROOT');
$package = getenv('PACKAGE_ROOT');
$path = $host.'/composer.json';
$composer = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
$composer['repositories'][] = [
    'type' => 'path',
    'url' => $package,
    'options' => ['symlink' => true],
];
file_put_contents($path, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
PHP

cd "$HOST_ROOT"
composer require digit7s/inertia-form:@dev inertiajs/inertia-laravel:^3.0 --no-interaction --prefer-dist
php artisan package:discover --ansi
php artisan list --raw | grep -Eq '^make:inertia-form([[:space:]]|$)'
php artisan make:inertia-form ConsumerSmokeForm --no-interaction
php -l app/Forms/ConsumerSmokeForm.php
if php artisan make:inertia-form LegacyModelForm --model=User --no-interaction >/dev/null 2>&1; then
    echo 'The removed --model generator option was unexpectedly accepted.' >&2
    exit 1
fi
if php artisan make:inertia-form --bogus --no-interaction >/dev/null 2>&1; then
    echo 'An unknown generator option was unexpectedly accepted.' >&2
    exit 1
fi

php artisan vendor:publish --tag=inertia-form-components --no-interaction
SOURCE_DIR="$PACKAGE_ROOT/resources/js/components"
PUBLISHED_DIR="$HOST_ROOT/resources/js/InertiaForm/components"
diff -ru "$SOURCE_DIR" "$PUBLISHED_DIR"

FIXTURE_ROOT="$PACKAGE_ROOT/tests/Integration/consumer-fixture"
cp "$FIXTURE_ROOT/bootstrap-app.php" bootstrap/app.php
cp -R "$FIXTURE_ROOT/app/Forms/." app/Forms/
cp -R "$FIXTURE_ROOT/app/Http/Controllers/." app/Http/Controllers/
cp -R "$FIXTURE_ROOT/app/Http/Requests/." app/Http/Requests/
cp -R "$FIXTURE_ROOT/app/Http/Middleware/." app/Http/Middleware/
cp -R "$FIXTURE_ROOT/app/Models/." app/Models/
cp -R "$FIXTURE_ROOT/database/migrations/." database/migrations/
cp "$FIXTURE_ROOT/routes/web.php" routes/web.php
cp -R "$FIXTURE_ROOT/resources/js/pages/." resources/js/pages/
cp "$FIXTURE_ROOT/resources/views/app.blade.php" resources/views/app.blade.php
cp "$FIXTURE_ROOT/InertiaFormIntegration.php" tests/Feature/InertiaFormIntegrationTest.php
mkdir -p tests/e2e tests/fixtures
cp "$FIXTURE_ROOT/tests/e2e/inertia-form.spec.ts" tests/e2e/inertia-form.spec.ts
cp "$FIXTURE_ROOT/tests/fixtures/upload.txt" tests/fixtures/upload.txt
cp "$FIXTURE_ROOT/playwright.config.ts" playwright.config.ts

php artisan migrate --force

npm install --save-exact --no-audit --no-fund vue@3.5.43 @inertiajs/vue3@3.9.1 @vitejs/plugin-vue@6.0.9 @playwright/test@1.64.0

cat > vite.config.js <<'JS'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }), tailwindcss(), vue()],
});
JS

mkdir -p resources/js/pages
cat > resources/js/app.js <<'JS'
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import InertiaForm from './InertiaForm/components/InertiaForm.vue';
import '../css/app.css';

const pages = import.meta.glob('./pages/**/*.vue');

createInertiaApp({
    resolve: (name) => pages[`./pages/${name}.vue`](),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('InertiaForm', InertiaForm)
            .mount(el);
    },
});
JS
cat > resources/js/pages/ConsumerSmoke.vue <<'VUE'
<script setup lang="ts">
defineProps<{ formPayload: Record<string, unknown> }>();
</script>

<template>
    <main><InertiaForm :form-payload="formPayload" submit-label="Save" /></main>
</template>
VUE

npm run build
php artisan test

if [[ "${RUN_BROWSER_TESTS:-0}" == "1" ]]; then
    if [[ "${CI:-false}" == "true" ]]; then
        npx playwright install --with-deps chromium
    else
        npx playwright install chromium
    fi
    npx playwright test
fi
