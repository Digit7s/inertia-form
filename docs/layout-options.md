# Layout Styles

InertiaForm allows you to customize the overall visual structure of your forms using the `style()` method.

## Default Layout

By default, forms render as a simple vertical stack of fields. This is ideal for modals, slide-overs, or simple pages.

```php
return UserForm::make($user)
    ->style('default') // Optional, as it's the default
    ->schema([
        // ...
    ]);
```

## Jetstream Layout

The `jetstream` style provides a more structured, two-column layout. It features a sticky title and description on the left, with the form fields contained within a white card on the right.

To use the Jetstream style, you must also provide a `title()` and `description()`.

```php
return ProfileForm::make($user)
    ->style('jetstream')
    ->title('Profile Information')
    ->description("Update your account's profile information and email address.")
    ->schema([
        TextInput::make('name')->required(),
        TextInput::make('email')->type('email')->required(),
    ]);
```

### Visual Structure
*   **Left Column**: Title (Heading 3) and Description (small text).
*   **Right Column**: 
    *   **Card Body**: A white/dark-card container with padding and rounded top corners.
    *   **Action Bar**: A gray/muted footer containing the submit button, with rounded bottom corners.

## Dynamic Customization

Since layout properties are serialized like any other form configuration, you can conditionally change them based on state or permissions:

```php
public function schema(): array
{
    $this->style(auth()->user()->prefers_side_layout ? 'jetstream' : 'default');
    
    // ...
}
```

## Methods Reference

| Method | Argument | Description |
| :--- | :--- | :--- |
| `style()` | `string` | Sets the layout style (`default` or `jetstream`). |
| `title()` | `string` | Sets the title (required for Jetstream). |
| `description()` | `string` | Sets the description (required for Jetstream). |
