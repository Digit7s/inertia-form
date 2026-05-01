# Select

The `Select` field provides a versatile dropdown for selecting items from a list. It supports searching, multiple selections, and automatic population via Eloquent relationships.

## Basic Usage
```php
use Digit7s\InertiaForm\Fields\Select;

Select::make('category_id')
    ->options([
        '1' => 'Technology',
        '2' => 'Health',
        '3' => 'Business',
    ]);
```

## Advanced Features
### Searchable & Multiple Selection
Enable searching within the dropdown or allow the user to select multiple items (rendered as pills on the frontend).
```php
Select::make('tags')
    ->multiple() // User can select many
    ->searchable() // Filter options with an input
    ->options([
        'fresh' => 'Freshman',
        'soph' => 'Sophomore',
        'jun' => 'Junior',
        'sen' => 'Senior',
    ]);
```

### Option Grouping
Pass a nested array to `options()` to render categorized groups with headers.
```php
Select::make('make')
    ->options([
        'Germany' => [
            'audi' => 'Audi',
            'bmw' => 'BMW',
            'mercedes' => 'Mercedes',
        ],
        'Japan' => [
            'toyota' => 'Toyota',
            'honda' => 'Honda',
            'nissan' => 'Nissan',
        ],
    ]);
```

### Relationship Population
Automatically fetch options from a database relationship. The package will resolve the query on the backend.
```php
Select::make('user_id')
    ->label('Assignee')
    ->relationship('users', 'name') // relationName, titleColumn
    ->searchable();
```
> **Backend Note:** When using `relationship()`, the `AbstractForm` uses Eloquent's `pluck()` to fetch the ID and the specified title column to populate the options array automatically.

## States
```php
Select::make('status')
    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
    ->disabled(); // User cannot interact
```
