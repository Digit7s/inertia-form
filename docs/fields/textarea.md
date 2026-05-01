# Textarea

The `Textarea` field allows users to enter multi-line text. It supports fluent configuration for height, width, for the native HTML5 character limits, and an automatic growth feature.

## Basic Usage

The default `Textarea` starts with 3 visible rows.

```php
use Digit7s\InertiaForm\Fields\Textarea;

Textarea::make('biography')
```

## Customizing Dimensions

You can set the initial visible number of lines and the width of the textarea.

```php
Textarea::make('comment')
    ->rows(10)
    ->cols(50)
```

## Autosizing

The `autosize()` feature allows the textarea to automatically adjust its height as the user types, providing a better user experience for varying lengths of content.

```php
Textarea::make('description')
    ->autosize()
```

## Character Limits

You can enforce minimum and maximum character lengths using `minLength()` and `maxLength()`.

```php
Textarea::make('summary')
    ->minLength(10)
    ->maxLength(500)
```

## Read-only and Disabled States

Like other fields, you can mark the textarea as read-only or disabled.

```php
Textarea::make('notes')
    ->readOnly()
    ->disabled()
```

## Configuration Options

| Method | Description |
| --- | --- |
| `rows(int $rows)` | The initial number of visible lines (default is `3`). |
| `cols(int $cols)` | The visible width of the textarea. |
| `autosize(bool $condition)` | Whether to automatically adjust height (default is `true` if called). |
| `minLength(int $length)` | The minimum number of characters required. |
| `maxLength(int $length)` | The maximum number of characters allowed. |
| `placeholder(string $text)` | The placeholder text. |
| `disabled(bool $condition)` | Whether to disable the field. |
| `readOnly(bool $condition)` | Whether to make the field read-only. |
