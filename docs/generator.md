# Artisan Form Generator

Generate a PHP form class with:

```bash
php artisan make:inertia-form ProfileForm
```

The command creates `app/Forms/ProfileForm.php` in the `App\Forms` namespace. It imports the package's `InertiaForm`, `TextInput`, and `Select` classes, and generates a basic `schema()` method. Edit that schema for the fields, authorization-aware options, and layout your application needs. The generator does not create routes, controllers, FormRequests, Vue pages, or database models.

Laravel's standard generator behavior protects an existing file: it reports that the destination already exists and leaves its contents unchanged. The command accepts a class name (including a namespaced name); use normal PHP class naming. Unknown options and missing names are reported by Artisan.

## `--model` compatibility note

Earlier versions exposed `--model` / `-m`, but the option was not used in the generated class or schema. It has been removed so the command no longer advertises behavior it does not perform. Calls such as `php artisan make:inertia-form ProfileForm --model=App\\Models\\User` now report an unknown option. Bind a model when using the form in your controller instead:

```php
ProfileForm::make($user)
    ->action(route('users.update', $user))
    ->toArray();
```
