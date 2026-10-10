# Select

The `Select` field provides a versatile dropdown for selecting items from a list. It supports searching, multiple selections, and population from explicit options or a constrained Eloquent relationship query.

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

### Relationship Options and Authorization
Relationship option queries must be explicitly constrained. `relationship()` names the Eloquent relation and label column; `relationshipOptions()` receives that relation's constrained Eloquent builder. Add the host application's authorization and tenant filters in the callback, and return the same builder instance:
```php
Select::make('user_id')
    ->label('Assignee')
    ->relationship('users', 'name')
    ->relationshipOptions(fn ($query) => $query
        ->where('tenant_id', auth()->user()->tenant_id)
        ->where('assignable', true)
        ->orderBy('name'), limit: 100)
    ->searchable();
```
The builder starts from the configured Eloquent relation, so relation constraints, pivot constraints, and model global scopes are preserved. The callback is still responsible for authorization and tenant filtering; global scopes alone are not an authorization boundary. `belongsTo`, `belongsToMany`, `hasMany`, and other Eloquent relations with a normal Eloquent query builder are supported. Relations that cannot provide that builder fail with an actionable exception.

The default maximum is 100 rows; set a limit from 1 to 500. This is intended for small option lists. Large datasets should use an explicit searchable/remote selection design in the host application rather than sending every record to the browser. If you already have a safe, bounded option list, `options([...])` remains supported and takes precedence even when relationship metadata is also present.

The `relationship()` method by itself no longer resolves records. Add `relationshipOptions()` or explicit `options()`; unbounded automatic loading is rejected. This is a security-related behavior change: migrate each existing relationship field and ensure the callback includes the current user's authorization and tenant constraints.

## States
```php
Select::make('status')
    ->options(['active' => 'Active', 'inactive' => 'Inactive'])
    ->disabled(); // User cannot interact
```
