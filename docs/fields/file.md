# File

`File` renders one native file input. A selected value is a browser `File` object; an existing string default is shown as the current file and is not assigned to the browser file input.

```php
use Digit7s\InertiaForm\Fields\File;

File::make('avatar')->label('Profile photo');
```

The component submits through Inertia's `useForm`. Inertia converts file-bearing form data to `FormData`. For a file-bearing `PUT`, `PATCH`, or `DELETE` form, this package sends a `POST` with Laravel's `_method` field set to the configured verb. The consuming Laravel application must validate the upload, authorize the operation, and store the file.

```php
public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'avatar' => ['nullable', 'file', 'image', 'max:5120'],
    ]);

    if (isset($validated['avatar'])) {
        $user->update(['avatar_path' => $validated['avatar']->store('avatars')]);
    }

    return back();
}
```

The field currently supports one file. It does not define `accept`, multiple-file, preview, or removal APIs.

Because there is no explicit remove-file control, an edit endpoint should normally leave the stored file path unchanged when no new upload is present. Only clear the stored path through a separate, explicit application action that is authorized and validated by Laravel. The component clears the browser's selected `File` after success/reset so it is not resubmitted accidentally; that client-side reset does not remove the file already stored by your application.
