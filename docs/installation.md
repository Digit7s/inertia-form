# Installation Guide

To integrate `InertiaForm` into your Laravel 12 / Inertia / Vue 3 workspace, follow these detailed steps.

## Step 1: Composer Configuration
Add the local package path to your root `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "packages/digit7s/*"
    }
],
"require": {
    "digit7s/inertia-form": "@dev"
}
```

## Step 2: Update Dependencies
Run the following command to symlink the package and register the service provider:
```bash
composer update digit7s/inertia-form
```

## Step 3: Publish Components
The package includes essential Vue components and fields that must be published to your local application directory so Vite can resolve them:
```bash
php artisan vendor:publish --tag=inertia-form-components
```

This will copy the components to `resources/js/InertiaForm`.

## Step 4: Component Auto-Import (Optional)
If you wish to make `InertiaForm` available globally, you can register it in your `app.ts` or `app.js` file:
```typescript
import InertiaForm from './InertiaForm/InertiaForm.vue';
app.component('InertiaForm', InertiaForm);
```
