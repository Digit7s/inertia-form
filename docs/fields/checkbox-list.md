# Checkbox List

The Checkbox List field allows users to select multiple options from a grid or list of checkboxes.

## Basic Usage

```php
use Digit7s\InertiaForm\Fields\CheckboxList;

CheckboxList::make('roles')
    ->options([
        'admin' => 'Administrator',
        'editor' => 'Editor',
        'viewer' => 'Viewer',
    ])
```

## Adding Descriptions

You can provide sub-text for each option to give users more context.

```php
CheckboxList::make('permissions')
    ->options([
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
    ])
    ->descriptions([
        'create' => 'Allows creating new resources.',
        'update' => 'Allows modifying existing resources.',
        'delete' => 'Allows permanent removal of resources.',
    ])
```

## Configuring Grid Columns

Control the responsive layout of the checkboxes using the `columns()` method.

```php
CheckboxList::make('interests')
    ->options([
        // ... many options
    ])
    ->columns([
        'default' => 1,
        'sm' => 2,
        'md' => 3,
        'lg' => 4,
    ])
```

Using a simple integer will apply that many columns across all screen sizes:

```php
CheckboxList::make('tags')
    ->columns(2)
```

## Searchable List

For long lists, enable the search feature to help users find options quickly.

```php
CheckboxList::make('countries')
    ->options($countries)
    ->searchable()
    ->searchPrompt('Search countries...')
```

## Bulk Actions

Enable a "Select All / Deselect All" button for convenience.

```php
CheckboxList::make('categories')
    ->options($categories)
    ->bulkToggleable()
```

## Conditional Disabling

Disable specific options based on custom logic.

```php
CheckboxList::make('plan_features')
    ->options($allFeatures)
    ->disableOptionWhen(fn ($key) => in_array($key, $premiumFeatures))
```

## Validation Constraints

Set minimum or maximum selection requirements. Note that these are hints for the frontend; ensure you also validate them on the server.

```php
CheckboxList::make('selections')
    ->minItems(1)
    ->maxItems(5)
```

## Methods Reference

| Method | Argument | Description |
| :--- | :--- | :--- |
| `options()` | `array \| Closure` | Key-value pairs of options. |
| `descriptions()` | `array \| Closure` | Sub-text for options. |
| `columns()` | `int \| array` | Grid layout configuration. |
| `bulkToggleable()` | `bool` | Enables Select All / Deselect All. |
| `searchable()` | `bool` | Enables search filter. |
| `searchPrompt()` | `string` | Search input placeholder. |
| `disableOptionWhen()` | `Closure` | Disables specific options. |
| `minItems()` | `int` | Minimum selection hint. |
| `maxItems()` | `int` | Maximum selection hint. |
