# Grid Layout
 
The `Grid` component allows you to arrange form fields into multiple columns. This is useful for grouping related fields side-by-side.
 
## Basic Usage
 
To create a grid, use the `Grid::make()` method and define its schema:
 
```php
use Digit7s\InertiaForm\Layouts\Grid;
use Digit7s\InertiaForm\Fields\TimePicker;
 
Grid::make(2)
    ->schema([
        TimePicker::make('start_time')
            ->label('Shift Start'),
        TimePicker::make('end_time')
            ->label('Shift End'),
    ])
```
 
The `make()` method accepts the number of columns. If not specified, it defaults to 2.
 
## Column Spanning
 
Individual fields can be configured to span multiple columns within a grid using the `columnSpan()` method:
 
```php
use Digit7s\InertiaForm\Fields\TextInput;
use Digit7s\InertiaForm\Fields\Textarea;
use Digit7s\InertiaForm\Layouts\Grid;
 
Grid::make(3)
    ->schema([
        TextInput::make('first_name'),
        TextInput::make('last_name'),
        TextInput::make('middle_name'),
        Textarea::make('biography')
            ->columnSpan(2), // Spans 2 out of 3 columns
        TextInput::make('website')
            ->columnSpan('full'), // Spans all columns in the grid
    ])
```
 
### Available Column Span Values
 
-   `int`: The number of columns to span (e.g., `2`).
-   `'full'`: Spans the entire width of the grid.
 
## Nested Grids
 
Grids can be nested inside other grids to create complex layouts:
 
```php
Grid::make(3)
    ->schema([
        TextInput::make('field_1'),
        Grid::make(2)
            ->columnSpan(2)
            ->schema([
                TextInput::make('nested_1'),
                TextInput::make('nested_2'),
            ]),
    ])
```
