## Resource Binding
You can bind an Eloquent model directly to the form. The form will automatically:
1. Detect if the model exists to choose between `POST` and `PUT`.
2. Map model attributes to field names for initial default values.

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
You can create your own field types by extending the base `Digit7s\InertiaForm\Fields\Field` class and providing a unique `$type` string that matches a Vue component on the frontend.
```php
namespace App\Forms\Fields;

use Digit7s\InertiaForm\Fields\Field;

class DatePicker extends Field
{
    protected string $type = 'datepicker';
}
```
Then create a matching component `InertiaForm/fields/DatePicker.vue` and register it in the main `InertiaForm.vue` component.
