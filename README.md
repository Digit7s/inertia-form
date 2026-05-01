# Inertia Form

A backend-driven form builder for Laravel, Inertia.js, and Vue 3. This package allows you to define form schemas in PHP, which are then dynamically rendered into an Inertia-ready Vue component using `useForm`.

## Features
- 🚀 **Backend-driven**: Define field schemas, labels, and defaults in PHP.
- 🎛️ **Fluent API**: Chainable methods for building inputs (e.g., `make()`, `label()`, `required()`).
- 🔄 **Dynamic Rendering**: Vue 3 component uses `@inertiajs/vue3`'s `useForm` for state management.
- 🎨 **Tailwind CSS + Shadcn**: Ready for modern UI styles.
- 🛠️ **Artisan Generator**: Scaffold new form classes instantly (`make:inertia-form`).

## Installation
Add the path repository to your `composer.json`:
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
Run `composer update digit7s/inertia-form` and publish the Vue components:
```bash
php artisan vendor:publish --tag=inertia-form-components
```

## Usage

### 1. Create a Form
```bash
php artisan make:inertia-form ProfileForm
```

### 2. Define the Schema with Resources
Forms can now be bound to Eloquent models or generic resources. The package will automatically detect the correct HTTP method (POST for new objects, PUT for existing ones) and attempt to populate default values from the model attributes.

```php
public function schema(): array
{
    return [
        TextInput::make('name')->label('Full Name')->required(),
        TextInput::make('email')->label('Email')->type('email'),
        Select::make('role')->options(['admin' => 'Admin', 'user' => 'User']),
    ];
}
```

### 3. Controller Integration
You can pass a model directly to the form's constructor or `make()` method.

```php
public function edit(User $user)
{
    return Inertia::render('Profile/Edit', [
        // Automatically populates defaults from $user and sets method to 'put'
        'formPayload' => UserProfileForm::make($user)
            ->action(route('profile.update'))
            ->toArray(),
    ]);
}
```

### 4. Vue Frontend
The frontend component is now strictly typed and auto-adaptive. It respects the backend-provided action and method while still allowing for prop-level overrides.

```vue
<template>
    <!-- method and action are auto-resolved from the payload -->
    <InertiaForm :form-payload="formPayload" />
</template>
```

---
[Installation Guide](docs/installation.md) | [Field Documentation](docs/fields.md)
