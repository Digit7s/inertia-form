# Date/Time Picker

The `DateTimePicker` field allows users to select a date and/or time. It supports both native browser pickers and a powerful custom Vue-based picker.

## Basic Usage

By default, the `DateTimePicker` shows both date and time pickers.

```php
use Digit7s\InertiaForm\Fields\DateTimePicker;

DateTimePicker::make('published_at')
```

### Date Picker

If you only need a date, use the `DatePicker` class:

```php
use Digit7s\InertiaForm\Fields\DatePicker;

DatePicker::make('date_of_birth')
```

### Time Picker

If you only need a time, use the `TimePicker` class:

```php
use Digit7s\InertiaForm\Fields\TimePicker;

TimePicker::make('starts_at')
```

## Customizing Formats

You can customize how the date is displayed in the UI and how it's formatted when sent to the server.

```php
DateTimePicker::make('appointment_at')
    ->displayFormat('d/m/Y H:i') // PHP date format for the UI
    ->format('Y-m-d H:i:s')       // Format for the server
```

## Setting Boundaries

You can restrict the selectable date range using `minDate()` and `maxDate()`:

```php
DatePicker::make('birthday')
    ->minDate(now()->subYears(100))
    ->maxDate(now())
```

## Native vs. Custom Pickers

By default, the field uses native browser pickers (`<input type="date">`, etc.). You can switch to a custom Vue-based picker for a more consistent cross-browser experience:

```php
DateTimePicker::make('event_at')
    ->native(false)
```

## Disabling Specific Dates

You can disable specific dates from being selected in the custom picker:

```php
DatePicker::make('appointment_date')
    ->native(false)
    ->disabledDates([
        '2024-12-25',
        '2025-01-01',
    ])
```

## Timezone Handling

You can specify a timezone for the picker:

```php
DateTimePicker::make('scheduled_at')
    ->timezone('UTC')
```

## Suffix Icon

The pickers come with a default icon on the right side of the input. `DatePicker` and `DateTimePicker` use a calendar icon, while `TimePicker` uses a clock icon.

### Customizing the Suffix Icon

You can override the default icon using the `suffixIcon()` method:

```php
use Digit7s\InertiaForm\Fields\DatePicker;

DatePicker::make('published_at')
    ->suffixIcon('calendar') // Use any supported icon name
```

## Precision & Steps

For time picking, you can enable seconds or customize the increments for hours, minutes, and seconds:

```php
DateTimePicker::make('precise_time')
    ->seconds()
    ->hoursStep(2)
    ->minutesStep(15)
    ->secondsStep(30)
```

## Configuration Options

| Method | Description |
| --- | --- |
| `displayFormat(string $format)` | The date format used in the UI. |
| `format(string $format)` | The format used when sending data to the server. |
| `suffixIcon(string $icon)` | Override the default suffix icon. |
| `minDate(string\|DateTime $date)` | The minimum selectable date. |
| `maxDate(string\|DateTime $date)` | The maximum selectable date. |
| `timezone(string $timezone)` | The timezone for the picker. |
| `native(bool $condition)` | Whether to use native browser pickers (default is `true`). |
| `seconds(bool $condition)` | Whether to show the seconds picker. |
| `hoursStep(int $step)` | The increment for the hours picker. |
| `minutesStep(int $step)` | The increment for the minutes picker. |
| `secondsStep(int $step)` | The increment for the seconds picker. |
| `disabledDates(array $dates)` | An array of dates that cannot be selected. |
| `firstDayOfWeek(int $day)` | The first day of the week (0 for Sunday). |
