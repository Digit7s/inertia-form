# Installation

## Requirements

The v2 Composer constraints declare PHP `^8.2` and Illuminate 12 or 13. Laravel 13 requires PHP 8.3 or newer. Laravel 11 support from v1.0.0 was dropped because Laravel's security-fix window ended on March 12, 2026. The consumer application supplies its Inertia server adapter, Vue 3, `@inertiajs/vue3`, and Laravel Vite configuration. The clean-host integration used Laravel 13.35.0, `inertiajs/inertia-laravel` 3.5.1, Vue 3.5.43, `@inertiajs/vue3` 3.9.1, Vite 7.3.7, `@vitejs/plugin-vue` 6.0.9, and Node 22.23.2. This verifies Inertia 3; Inertia 2 is not part of the documented support contract. GitHub Actions passed PHP 8.2.34 with Laravel 12.69.3, PHP 8.3.35 with Laravel 13.35.0, and PHP 8.5.11 with Laravel 13.35.0. The clean Laravel 13 integration also passed on PHP 8.3.35.

The published form components use native HTML elements and Tailwind utility class names. Tailwind is optional for compilation and behavior; without it, controls render with browser-native styling and the utility classes have no effect. For the default spacing and control styling, include `resources/js/InertiaForm/components/**/*.vue` in the host Tailwind content/source scan. The package does not import shadcn-vue, `@/` aliases, icon packages, app-specific composables, or a global stylesheet. The host application needs `vue`, `@inertiajs/vue3`, `@vitejs/plugin-vue`, and `inertiajs/inertia-laravel` 3, plus the host's normal Laravel Vite setup. These are host dependencies rather than Composer runtime dependencies of this PHP library. Vite 7's Node requirement is Node 20.19+ or 22.12+; the package frontend suite currently runs on Node 22.

For Tailwind CSS 4, if automatic source detection does not include the published tree, add this to `resources/css/app.css`:

```css
@source "../js/InertiaForm/components";
```

For Tailwind CSS 3, include the published Vue files in `tailwind.config.js`:

```js
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
};
```

## Install from Packagist

In a Laravel application that already uses Inertia and Vue 3:

```bash
composer require digit7s/inertia-form
php artisan vendor:publish --tag=inertia-form-components
```

Laravel package discovery registers the service provider and the `make:inertia-form` command. No provider registration is needed in a standard Laravel app with package discovery enabled.

The publisher writes the component tree to:

```text
resources/js/InertiaForm/components/
```

It publishes the Vue files and their TypeScript helpers, including the field registry. It does not publish tests or require an application alias. Run your normal Vite build after publishing.

To reproduce a minimal Laravel 13 local-path install, provider and command discovery, publishing, and Vue build from a clean temporary application, run this from the package checkout:

```bash
bash scripts/ci/clean-consumer-smoke.sh
```

The smoke script uses Laravel's standard application skeleton, a local Composer path repository, the real Inertia Laravel 3 and Vue adapter packages, the committed integration fixture, real Laravel validation and persistence tests, and a Vite production build. Set `RUN_BROWSER_TESTS=1` to also install Playwright Chromium and run the browser smoke test. The script removes only the temporary directory it creates when it exits.

## Import the component

In the app entry file (commonly `resources/js/app.ts` or `resources/js/app.js`), import the published component and register it with the Vue app created by `createInertiaApp`:

```ts
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import InertiaForm from './InertiaForm/components/InertiaForm.vue';

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
```

The example assumes `app.ts` is at `resources/js/app.ts`, pages are under `resources/js/pages` as in the Inertia Laravel 3 default configuration, and the Laravel Vite Vue plugin is already configured. If your app uses a different page resolver or app entry, keep that setup and adjust only the relative import path. Use the real Inertia Vue adapter and the same `createInertiaApp` setup your other pages use.

A page can then render the form with the server-provided payload:

```vue
<script setup lang="ts">
defineProps<{ formPayload: Record<string, unknown> }>();
</script>

<template>
  <InertiaForm :form-payload="formPayload" submit-label="Save" />
</template>
```

See the [form lifecycle guide](form-lifecycle.md) for controller, FormRequest, create, and edit examples.

## Local path repository for package development

A local path repository is a development option for testing unreleased package changes. It is not the normal consumer installation method and does not publish the package to Packagist.

For a sibling checkout at `../inertia-form`, add this repository to the host app's `composer.json` and require the development branch:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../inertia-form",
      "options": { "symlink": true }
    }
  ],
  "require": {
    "digit7s/inertia-form": "@dev"
  }
}
```

Then run `composer update digit7s/inertia-form` and the publish command above. Composer symlinks the PHP package source in a local checkout; the Vue components remain copied into the host app and must be republished when you choose to update them.

## Component updates

Published Vue files are consumer-owned copies. A Composer package update does not change them. The normal publish command skips files that already exist; use `--force` only when you intend to overwrite them.

Before updating a customized component, compare the current copy with the package version and keep the diff for review:

```bash
diff -u vendor/digit7s/inertia-form/resources/js/components/fields/TextInput.vue \
  resources/js/InertiaForm/components/fields/TextInput.vue
```

For a complete tree comparison, use `diff -ru` with those same source and destination directories. Back up or commit your consumer-owned component changes, review the source-to-copy diff, then publish without `--force` to copy new files while preserving existing customizations. If an existing file needs a change, merge it deliberately. Do not use `--force` on a customized tree unless you have saved and reviewed the local version.
