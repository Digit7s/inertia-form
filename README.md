# Inertia Form Builder for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/digit7s/inertia-form.svg?style=flat-square)](https://packagist.org/packages/digit7s/inertia-form)
[![Total Downloads](https://img.shields.io/packagist/dt/digit7s/inertia-form.svg?style=flat-square)](https://packagist.org/packages/digit7s/inertia-form)
[![License](https://img.shields.io/packagist/l/digit7s/inertia-form.svg?style=flat-square)](https://packagist.org/packages/digit7s/inertia-form)

A fluent, backend-driven form builder for Laravel and Inertia.js (Vue 3). Define your forms, layouts, and validation logic entirely in PHP and render them dynamically in your Vue frontend with high-quality components.

## Requirements

- **PHP**: 8.2+
- **Laravel**: 11.0+ / 12.0+
- **Inertia.js**: Vue 3 adapter
- **Tailwind CSS**: (Recommended for default styling)

## Installation

### 1. Backend (PHP)
Install the package via Composer:

```bash
composer require digit7s/inertia-form
```

### 2. Frontend (Vue 3)
You can publish the Vue components directly into your project using the Laravel Service Provider:

```bash
php artisan vendor:publish --tag=inertia-form-components
```

## Setup & Configuration

### Registering Components
Import and register the `InertiaForm` component in your `app.js` or directly within your Vue pages.

**Global Registration (`app.js`):**

```javascript
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import InertiaForm from './InertiaForm/components/InertiaForm.vue';

createInertiaApp({
  resolve: name => {
    // ...
  },
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
            ->action(route('profile.update'))
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
            :formPayload="formPayload" 
            submitLabel="Update Profile"
        />
    </div>
</template>
```

## Available Fields
- `TextInput` (text, email, password, etc.)
- `Select` (searchable, relationship-bound)
- `DatePicker` / `TimePicker` / `DateTimePicker`
- `Textarea`
- `CheckboxList`
- `Grid` (Layout)

## Documentation
For more detailed information on fields, layouts, and advanced features, please refer to the documentation:

- [Fields & Options](docs/fields.md)
- [Layouts & Grids](docs/layout-options.md)
- [Handling Redirects](docs/redirects.md)

## License
The MIT License (MIT). Please see [License File](LICENSE) for more information.
