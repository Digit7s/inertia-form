# Inertia Form Builder for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/digit7s/inertia-form.svg?style=flat-square)](https://packagist.org/packages/digit7s/inertia-form)
[![Total Downloads](https://img.shields.io/packagist/dt/digit7s/inertia-form.svg?style=flat-square)](https://packagist.org/packages/digit7s/inertia-form)
[![License](https://img.shields.io/packagist/l/digit7s/inertia-form.svg?style=flat-square)](https://packagist.org/packages/digit7s/inertia-form)

A fluent, backend-driven form builder for Laravel and Inertia.js (Vue 3). Define form schemas in PHP and render them with published Vue components. Laravel validation, authorization, persistence, and safe handling of rich text remain in the host application.

## Requirements and Compatibility

- **PHP**: `^8.2` in the package Composer constraints. Use the PHP version required by your chosen Laravel release (Laravel 13 requires PHP 8.3+).
- **Laravel**: The package Composer constraints support Laravel 12 and 13 through Illuminate components. Laravel 11 is not supported. GitHub Actions verified PHP `8.2.34` with Laravel `12.69.3`, PHP `8.3.35` with Laravel `13.35.0`, and PHP `8.5.11` with Laravel `13.35.0`. The clean Laravel 13 consumer also passed on PHP `8.3.35`.
- **PHP lifecycle**: PHP 8.2 remains supported through Laravel 12. Its upstream security-support period ends December 31, 2026, so the PHP minimum should be reviewed after that date.
- **Inertia**: The consuming application must install and configure `inertiajs/inertia-laravel` and `@inertiajs/vue3`. Inertia 3 was verified with `inertiajs/inertia-laravel 3.5.1` and `@inertiajs/vue3 3.9.1`. Inertia 2 has not been verified and is outside the currently documented support contract.
- **Frontend**: Vue `3.5.43`, Vite `7.3.7`, `@vitejs/plugin-vue 6.0.9`, and Node `22.23.2` were used for the clean-host build. Vite 7 requires Node `20.19+` or `22.12+`; install `vue`, `@inertiajs/vue3`, and the Vue Vite plugin if your app does not already provide them.
- **Styling**: Components use native HTML controls plus Tailwind utility class names. Tailwind is not required to build or submit forms, but include the published component tree in the host Tailwind source/content scan to receive the documented spacing and control styling. There are no shadcn-vue, app-local UI component, icon-library, or global stylesheet imports.

The version numbers above record tested versions. The Laravel/PHP combinations listed here have passed the GitHub Actions compatibility matrix; Inertia 3 has passed the clean-consumer integration, while Inertia 2 remains outside the documented support contract.

For package development, use the local path instructions in the installation guide. Normal application installation uses Packagist.

## Installation

Install the published package through Packagist:

```bash
composer require digit7s/inertia-form
```

Laravel's package discovery registers the service provider and `make:inertia-form` command. Publish the Vue components:

```bash
php artisan vendor:publish --tag=inertia-form-components
```

The files are published under `resources/js/InertiaForm/components`. The host app must already have Laravel Vite, Vue 3, `@vitejs/plugin-vue`, Inertia Laravel 3, and `@inertiajs/vue3` configured. See the [installation guide](docs/installation.md) for the exact app entry-point import and a local path-repository option for package development.

## Setup & Configuration

### Registering Components
Import and register the `InertiaForm` component in your `app.js` or directly within your Vue pages.

**Global Registration (`app.js`):**

```javascript
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import InertiaForm from './InertiaForm/components/InertiaForm.vue';

const pages = import.meta.glob('./pages/**/*.vue');

createInertiaApp({
  resolve: name => pages[`./pages/${name}.vue`](),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .component('InertiaForm', InertiaForm) // Register globally
      .mount(el);
  },
});
```

## Basic Usage

### 1. Generate a Form Class
Use the Artisan command to scaffold a new form:

```bash
php artisan make:inertia-form ProfileForm
```

### 2. Define the Schema
In your newly created form class (`app/Forms/ProfileForm.php`), define the fields and layout:

```php
namespace App\Forms;

use Digit7s\InertiaForm\InertiaForm;
use Digit7s\InertiaForm\Fields\TextInput;
use Digit7s\InertiaForm\Fields\Select;

class ProfileForm extends InertiaForm
{
    public function schema(): array
    {
        return [
            TextInput::make('name')
                ->label('Full Name')
                ->placeholder('John Doe')
                ->required(),

            TextInput::make('email')
                ->email()
                ->label('Email Address')
                ->required(),

            Select::make('country')
                ->options([
                    'us' => 'United States',
                    'ca' => 'Canada',
                    'uk' => 'United Kingdom',
                ])
                ->searchable(),
        ];
    }
}
```

### 3. Return from Controller
Pass the form payload to your Inertia page:

```php
use App\Forms\ProfileForm;
use App\Models\User;

public function edit(User $user)
{
    return inertia('Profile/Edit', [
        'formPayload' => ProfileForm::make($user)
            ->action(route('profile.update', $user))
            ->toArray(),
    ]);
}
```

### 4. Render in Vue
Use the provided `<InertiaForm />` component to render the form:

```vue
<script setup>
defineProps({
    formPayload: Object,
});
</script>

<template>
    <div class="max-w-2xl mx-auto py-10">
        <InertiaForm
            :form-payload="formPayload"
            submit-label="Update Profile"
        />
    </div>
</template>
```

## Available Fields
- `TextInput` (text, email, password, etc.)
- `Textarea`
- `Select` (searchable, relationship-bound)
- `CheckboxList`
- `DatePicker` / `TimePicker` / `DateTimePicker` (native browser controls)
- `File` (single file upload through Inertia)
- `Grid` (including nested layouts)
- `Radio`, `RichTextEditor`, and `MarkdownEditor`

`required()` and HTML constraints are browser hints. Define authorization, file rules, and server-side validation in Laravel; Inertia returns validation errors to the form helper.

## Security Boundaries

- Relationship-backed selects require `relationshipOptions()` with explicit host-application authorization and tenant constraints. The query starts from the Eloquent relation and preserves relation constraints and model scopes; global scopes alone do not authorize access. Option lists are capped at 100 by default and at 500 maximum. See [Select relationship options](docs/fields/select.md).
- Password-type fields and common token, secret, key, and credential fields are not automatically hydrated from models. `hydrateSensitiveDefault()` is an explicit opt-in that sends the value to the browser; use it only when the user is authorized to see that value.
- The RichTextEditor is a safe textarea fallback, not a sanitizer. Its value is untrusted HTML. The consuming application must sanitize before persistence and render stored markup through a safe HTML rendering path.
- Frontend `required` and other constraints are usability hints. Enforce authorization and validation on the Laravel server, including uploaded files and every submitted relationship identifier.

## Documentation
For more detailed information on fields, layouts, and advanced features, please refer to the documentation:

- [Fields & Options](docs/fields.md)
- [Validation and authorization](docs/validation.md)
- [Relationship options and authorization](docs/relationships.md)
- [Form lifecycle and submission](docs/form-lifecycle.md)
- [Layouts & Grids](docs/layout-options.md)
- [Handling Redirects](docs/redirects.md)
- [File uploads](docs/fields/file.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Artisan generator](docs/generator.md)
- [Upgrade from v1.0.0](UPGRADE.md)
- [Changelog](CHANGELOG.md)
- [Security policy](SECURITY.md)
- [Contributing](CONTRIBUTING.md)

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
