# Redirecting After Submission

By default, InertiaForm remains on the same page after a successful submission, preserving the form state and scroll position. You can customize this behavior using the `redirect()` method.

## Default Behavior

If no redirect is specified, the form stays on the current page. This is ideal for quick edits or single-page workflows.

## Basic Redirect

To redirect to a specific URL after a successful save, provide a string to the `redirect()` method:

```php
return UserForm::make($user)
    ->redirect('/users')
    ->schema([
        // ...
    ]);
```

## Using Laravel Routes

You can use the `route()` helper to generate the redirect URL:

```php
return UserForm::make($user)
    ->redirect(route('users.index'))
    ->schema([
        // ...
    ]);
```

## Dynamic Redirects (Closures)

If you need to calculate the redirect URL based on the saved model (for example, redirecting to the edit page of a newly created record), you can pass a Closure:

```php
return UserForm::make(new User)
    ->redirect(fn (User $user) => route('users.edit', $user))
    ->schema([
        // ...
    ]);
```

The Closure receives the current resource (the model) as its only argument.

## Controller Implementation

To use the redirect URL in your controller, call the `getRedirectUrl()` method on your form instance. If it returns a URL, use Laravel's `redirect()->to()` method. If it's null, you can default to `back()` or another fallback.

```php
public function store(Request $request)
{
    $form = UserForm::make(new User);
    
    // ... validation and saving logic ...
    
    $redirectUrl = $form->getRedirectUrl();
    
    if ($redirectUrl) {
        return redirect()->to($redirectUrl)->with('success', 'Saved!');
    }
    
    return back()->with('success', 'Saved!');
}
```

## Methods Reference

| Method | Argument | Description |
| :--- | :--- | :--- |
| `redirect()` | `string \| Closure \| null` | Sets the post-submission destination. |
| `getRedirectUrl()` | (none) | Resolves the URL (evaluates Closures). |
