# TextInput

The `TextInput` field is the primary method for gathering string and numeric data from users. It provides a wide range of browser features, such as character limits, input types, and affixes.

## Basic Usage
```php
use Digit7s\InertiaForm\Fields\TextInput;

TextInput::make('name')
    ->label('Full Name')
    ->placeholder('John Doe')
    ->required();
```

## Setting the Input Type
The field supports various HTML input types via specific fluent methods.
```php
TextInput::make('email')->email();
TextInput::make('password')->password();
TextInput::make('age')->numeric();
TextInput::make('website')->url();
TextInput::make('phone')->tel();
```
> **Tip:** When using `password()`, the frontend component automatically provides a "show/hide" eye toggle to the user.

## Adding Affixes
Use prefixes and suffixes to provide context to the input without changing the underlying value.
```php
TextInput::make('price')
    ->numeric()
    ->prefix('$') // Displays "$" on the left
    ->suffix('USD'); // Displays "USD" on the right
```

## Length & Numeric Validations
These methods apply standard HTML5 attributes to the input for browser-level validation and hinting.
```php
// Character lengths
TextInput::make('bio')
    ->minLength(10)
    ->maxLength(250);

// Numeric limits
TextInput::make('quantity')
    ->numeric()
    ->minValue(1)
    ->maxValue(99)
    ->step(0.5);
```

## States & Browser Features
Control how the user interacts with the field and set browser behaviors.
```php
TextInput::make('id')
    ->disabled(); // User cannot interact or focus the field

TextInput::make('slug')
    ->readOnly(); // User can focus/scroll but cannot change text

TextInput::make('first_name')
    ->autofocus() // Automatically focus the field on page load
    ->autocomplete('given-name'); // Hint the browser's autofill
```
