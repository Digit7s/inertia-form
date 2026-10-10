## Resource Binding
You can bind an Eloquent model directly to the form. The form will automatically:
1. Detect if the model exists to choose between `POST` and `PUT`.
2. Map declared model attributes and accessors to field names for initial default values. Null model values override field defaults. Undeclared model relationships are not probed.

```php
public function edit(User $user)
{
    return Inertia::render('Users/Edit', [
        'form' => UserForm::make($user)
            ->action(route('users.update', $user))
            ->toArray(),
    ]);
}
```

## Common Field API
| Method | Description |
| --- | --- |
| `make(string $name)` | Static constructor for the field. |
| `label(string $label)` | Set the field label (defaults to headline-case of the name). |
| `default(mixed $value)` | The initial value passed to Inertia's `useForm`. |
| `required(bool $required = true)` | Marks the field as required on the frontend. |
| `placeholder(string $placeholder)` | Placeholder text for the input. |
| `meta(string $key, mixed $value)` | Add custom metadata for the field. |

## Supported Built-in Fields

The currently published source maps these schema types to native Vue controls:

- `TextInput`: text, email, password, numeric, telephone, URL, and color input types.
- `Textarea`: multi-line plain text.
- `Select`: single or multiple select from explicit options, with optional search; relationship options require an explicit constrained query as described in [select documentation](fields/select.md).
- `CheckboxList` and `Radio`: explicit option lists.
- `DatePicker`, `TimePicker`, and `DateTimePicker`: native browser date/time inputs.
- `File`: one browser `File` upload.
- `RichTextEditor` and `MarkdownEditor`: safe textarea fallbacks; neither renders HTML or provides a sanitizer/editor runtime.
- `Grid`: presentational nested field layout.

`required()` and the other HTML constraints only affect browser interaction. Use Laravel `FormRequest` rules for server validation and authorization. The package serializes a schema and defaults; the consuming controller, routes, authorization, validation, and persistence are application responsibilities.

## TextInput
The default input for string values, supporting HTML types like `text`, `password`, `email`, and `number`.
```php
TextInput::make('password')
    ->type('password')
    ->required()
    ->placeholder('Enter your secret password');
```

## Select
Renders a dropdown of options.
```php
Select::make('country')
    ->label('Select Country')
    ->options([
        'US' => 'United States',
        'CA' => 'Canada',
        'GB' => 'United Kingdom',
    ])
    ->default('US');
```

## Extending Fields
You can create your own field types by extending the base `Digit7s\InertiaForm\Fields\Field` class and providing a unique `$type` string. A matching Vue component must be added to the published frontend and registered in `resources/js/components/fieldRegistry.ts`.
```php
namespace App\Forms\Fields;

use Digit7s\InertiaForm\Fields\Field;

class DatePicker extends Field
{
    protected string $type = 'datepicker';
}
```
Then create a matching component under `resources/js/components/fields/` and add its import and type mapping to `fieldRegistry.ts`. Unknown types render an explicit unsupported-field message; they do not fall back to a text input.

`required()` and field constraints are client-side hints only. Laravel authorization, validation, and persistence remain the consuming application's responsibility.

### Sensitive Defaults
Password-type inputs and common password, token, secret, key, and credential field names are excluded from automatic model hydration. To explicitly hydrate another sensitive value, opt in on that field:

```php
TextInput::make('integration_secret')
    ->sensitive()
    ->hydrateSensitiveDefault();
```

This sends the value to the browser. Only opt in when the current user is authorized to receive it. Prefer not to send credentials or secrets to a form at all. Set an explicit `default()` when a form needs a deliberate initial value. Laravel authorization and validation must still protect the submitted value.

### Rich Text Values
`RichTextEditor` currently uses a textarea fallback. It preserves and edits the supplied string as text; it does not sanitize, preview, or make HTML safe. Treat stored and submitted HTML as untrusted. Sanitize at the consuming application's server-side persistence boundary with a maintained HTML sanitizer appropriate to the application's allowed markup, and render stored content with a safe HTML rendering path that does not inject unsanitized strings using `v-html` or equivalent. Laravel validation and authorization remain application responsibilities.
