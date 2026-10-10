# Troubleshooting

## Vite cannot resolve the component import

Confirm that `vendor:publish` completed and that the file exists at `resources/js/InertiaForm/components/InertiaForm.vue`. From `resources/js/app.ts` or `app.js`, the usual import is:

```ts
import InertiaForm from './InertiaForm/components/InertiaForm.vue';
```

Adjust the relative path if your app entry file is in another directory. The package does not create an `@` alias.

## Vue or Inertia imports are missing

The host application supplies `vue`, `@inertiajs/vue3`, and its Vue-enabled Vite setup. Install the frontend dependencies used by your Laravel/Inertia application and verify its standard app entry imports `createInertiaApp` from `@inertiajs/vue3`. Tailwind, shadcn-vue, icon packages, and app-local composables are not required by these published components.

## The Artisan command or publish tag is unknown

Run `composer show digit7s/inertia-form`, confirm Laravel package discovery is enabled, and run `composer dump-autoload`. The service provider registers `make:inertia-form` and the `inertia-form-components` publish tag when the package is installed in a Laravel application.

## A relationship select fails or returns too many options

Add `relationshipOptions()` and apply the current user's authorization and tenant constraints to the relation builder. The callback must return the supplied builder, and the result is limited to 100 options by default (maximum 500). See [relationship options](fields/select.md).

## A server validation error is not shown

Return the normal Laravel redirect after a failed FormRequest validation and keep the Inertia middleware in the web middleware stack. Ensure the error key matches the PHP field name; dotted names such as `details.reference` are supported. `required()` is a browser hint and does not replace Laravel validation.

## A customized published file needs an update

Composer updates the PHP package but does not refresh the copied Vue files. Compare your copy with the installed package, preserve your changes, then publish reviewed updates. A normal publish does not force-overwrite existing files; `--force` overwrites consumer files and should only be used after backing up and reviewing them. See [installation and component updates](installation.md#component-updates).

## Files are missing after a `PUT` or `PATCH`

Inertia sends a `POST` with Laravel's `_method` field when submitted data contains a file and the configured verb is `PUT`, `PATCH`, or `DELETE`. Confirm the route accepts that verb, the FormRequest validates the field as a file, and the application stores the returned `UploadedFile`. See the [file guide](fields/file.md).
