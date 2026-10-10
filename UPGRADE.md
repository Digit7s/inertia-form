# Upgrading from v1.0.0 to v2.0.0

The current development branch contains deliberate changes to published PHP and Vue behavior. It is prepared as **v2.0.0** because several of those changes require consumer code or published components to change. This guide describes the release candidate behavior; v2.0.0 has not been published.

## 1. Relationship select options must be explicit and bounded

In v1, `relationship()` automatically queried every related row with no limit or consumer authorization callback. In v2, a relationship select must provide either an explicit `options()` list or a constrained callback. The callback receives the relationship's Eloquent builder. Apply tenant and authorization filters, cap the result, and select only the label and key columns needed by the form.

```php
// v1: automatically loaded all related rows
Select::make('team_id')->relationship('team', 'name');

// v2: constrain the rows visible to this user
Select::make('team_id')
    ->relationship('team', 'name')
    ->relationshipOptions(function ($query) {
        return $query->where('teams.account_id', auth()->user()->account_id);
    }, limit: 100);

// Or send a deliberately selected list
Select::make('team_id')->options($authorizedTeams->pluck('name', 'id')->all());
```

The callback does not authorize submitted IDs. The controller or FormRequest must authorize the selected team again on submission and must validate that it belongs to the current account.

## 2. Sensitive model values are no longer hydrated by default

In v1, model properties were read for every field except a field literally named `password`; reading could also trigger undeclared relationship access. In v2, default hydration is limited to declared model attributes or explicitly configured select relationships, and common sensitive names and password-type fields are excluded unless explicitly opted in. Review schemas containing API keys, credentials, access tokens, recovery codes, computed values, or other private data. Add a deliberate `default()` for computed values, and configure relationship fields explicitly.

```php
// v1 behavior: an arbitrary sensitive property could be sent to the browser
TextInput::make('api_token');

// v2: keep the field blank when editing
TextInput::make('api_token')->sensitive();

// Only when intentionally disclosing an existing value is safe:
TextInput::make('display_name')->sensitive()->hydrateSensitiveDefault();
```

Never hydrate a password or secret merely to prefill an edit form. Laravel authorization and request validation remain application responsibilities.

## 3. The generator no longer accepts `--model` / `-m`

The v1 command declared this option but did not use it in the generated class. Remove the option from scripts and documentation:

```sh
# v1
php artisan make:inertia-form ProfileForm --model=User

# v2
php artisan make:inertia-form ProfileForm
```

The generated class is placed under `App\Forms` and the application supplies its resource when calling `ProfileForm::make($user)`. Passing the old option now produces Artisan's normal invalid-option error.

## 4. Dotted names are nested data paths

In v1, a field name such as `address.city` was treated as a literal property key. In v2, dotted names map to nested form values and Laravel's matching dotted validation keys:

```php
TextInput::make('address.city');
```

The request now contains `address[city]` (PHP array `address => ['city' => ...]`). Update controller reads, validation rules, mass assignment, and tests from `$request->input('address.city')`-as-literal-key assumptions to Laravel's normal dot-notation access, such as `$request->input('address.city')` which reads the nested value. Do not name a field with a dot when a literal key containing a dot is intended.

## 5. Record identity transitions protect dirty forms

InertiaForm now tracks an identity from `formKey`, action, and method. If the server changes the action identity while local edits are dirty, submission pauses and the component preserves those values until the user chooses to discard and load the new record. On successful create-to-edit redirects, the new server payload becomes the baseline.

For pages where multiple records share an action URL, pass a stable explicit key:

```vue
<InertiaForm :form-payload="formPayload" :form-key="`user:${user.id}`" />
```

Review custom navigation code that previously expected incoming props to silently replace dirty values.

## 6. New optional Vue props and exposed controls

The component retains `formPayload`, `submitLabel`, `action`, and `method`. It adds optional `formKey`, `errorBag`, and `resetOnSuccess` props. The default slot's `actions` props now include `reset` and `processing`; the component exposes `form`, `reset()`, and `submit()` to template refs. Existing custom action slots should accept the added values as optional.

## 7. File uploads use POST plus `_method` for PUT/PATCH/DELETE

When submitted data contains a file and the requested verb is PUT, PATCH, or DELETE, InertiaForm sends a multipart POST with `_method` spoofing, as required by standard PHP/Laravel file handling. The effective route and validation method remain the configured verb. Update custom middleware or request-signature logic that assumes the wire-level method is always PUT/PATCH. Successful submissions clear the browser file selection while retaining the saved path returned by the server; a missing replacement file should preserve the existing model path in application code.

## 8. Rich-text rendering falls back to plain text

The built-in rich-text field was added after the v1.0.0 tag; its fallback is a normal textarea and it does not render submitted values as HTML. If you adopted the unreleased earlier field implementation, it no longer uses any editor or UI library dependency. Applications that add an editor must sanitize HTML on the server before storage and use a vetted renderer with an explicit sanitization policy. Do not restore `v-html` for form values.

## 9. Republish the new Vue component set

v2 publishes source files from `resources/js/components` into `resources/js/InertiaForm/components`. Composer updates the PHP package but do not update these copied Vue files. The normal `vendor:publish` command skips files that already exist and copies newly added files; `--force` overwrites matching consumer files. Compare package sources with your published copies, preserve or commit your customizations, and merge changes to customized files deliberately. See [installation and updates](docs/installation.md#component-updates).

## 10. Laravel 11 support has ended

The v1 package accepted Laravel 11. The v2 Composer constraints accept Laravel 12 and 13 only. Laravel 11's upstream security-fix window ended on March 12, 2026, and Composer's current security policy blocks dependency resolution for the available Laravel 11 release. This intentionally narrows the install range for v2. PHP 8.2 remains supported through Laravel 12; PHP 8.2's own security-support period ends December 31, 2026, so review the minimum PHP version after that date.

If your application remains on Laravel 11, stay on the v1 package line while planning a framework upgrade; v2 will not resolve with Laravel 11.

## 11. Other reviewed contracts

- `Grid` and built-in fields gain nested schema serialization and native Vue implementations. Rebuild after republishing them.
- Select options preserve explicit labels, but relationship loading is bounded and no longer ignores relationship constraints.
- The package does not perform server-side validation, authorization, rich-text sanitization, or database persistence. Those remain application code.
- Laravel 13 support was added after v1.0.0. The v1.0.0 Packagist release only declares Laravel 11 and 12. The v2 Laravel 12/13 matrix has passed on PHP 8.2, 8.3, and 8.5 as listed in the README.
