# Form Lifecycle and Submission

`InertiaForm` uses Inertia's Vue `useForm` helper. It does not generate Laravel validation rules; validate and authorize every request in the consuming application.

## Create and Edit

```php
// Create action
$form = UserForm::make()
    ->action(route('users.store'))
    ->method('post')
    ->toArray();

// Edit action
$form = UserForm::make($user)
    ->action(route('users.update', $user))
    ->method('patch')
    ->toArray();
```

The component method/action props override values in `formPayload`. Supported methods are `post`, `put`, `patch`, and `delete`; an unsupported method or missing action is shown as an accessible form error and does not submit. Keyboard submission uses the native `<form>` submit event. While an Inertia request is active, duplicate submits are ignored and the default submit button is disabled.

## Record Identity and Incoming Props

Pass a stable `formKey` for each record identity. If omitted, the component uses the resolved action and method as its identity.

```vue
<InertiaForm
  :form-payload="formPayload"
  :form-key="`users:${user.id}`"
  submit-label="Save user"
/>
```

For a clean form, new defaults for the same identity are adopted. Dirty values are preserved when the same identity receives updated defaults; newly introduced fields receive their defaults. When the identity changes while dirty, the current values and schema remain in place, submission is paused, and an explicit “Discard these changes and load the new record” action is shown. Confirming loads the new schema and defaults and clears errors. Unrelated props such as `submitLabel` do not reset form state. If you deliberately remount the component with Vue's `:key`, that remount starts a fresh form instance; do so only when discarding local edits is intended.

PHP field names containing dots are treated as nested data paths. Nested grids do not change field paths.

## Reset and Success

The form instance is available in the existing `actions` slot. A reset button can call the exposed reset callback:

```vue
<InertiaForm :form-payload="formPayload">
  <template #actions="{ form, reset, processing }">
    <button type="button" @click="reset">Reset</button>
    <button type="submit" :disabled="processing">
      {{ processing ? 'Saving…' : 'Save' }}
    </button>
  </template>
</InertiaForm>
```

The default `resetOnSuccess` is `false`: ordinary values stay visible and Inertia marks the submitted values as the new reset baseline. Selected `File` values and password inputs are cleared after success so a later request does not upload an old file or retain a submitted password. Set `reset-on-success` to `true` to restore current defaults and clear errors after success; transient file and password values are still cleared. Server-returned defaults for the same identity are adopted when the form is clean. Manual reset restores the current defaults, clears field errors, and clears selected file and password inputs.

## Laravel Validation and Error Bags

Use normal FormRequest rules. Inertia returns validation errors to `useForm`; the component displays the matching field error (including dotted nested names), clears that field's error when edited, preserves values on failure, and focuses the first invalid control.

```php
public function update(UpdateUserRequest $request, User $user)
{
    $user->update($request->validated());

    return to_route('users.edit', $user);
}
```

```vue
<InertiaForm
  :form-payload="formPayload"
  :form-key="`users:${user.id}`"
  error-bag="updateUser"
/>
```

`errorBag` is passed as an Inertia visit option, which is useful when multiple forms use the same field names. Laravel authorization and validation remain server responsibilities. Frontend `required()` metadata is only a browser hint.

## Files and Request Failures

Inertia automatically converts nested `File` values to `FormData`. For file-bearing `put`, `patch`, or `delete` submissions, the component submits a `post` request with Laravel's `_method` spoof field. It removes any stale `_method` value from form state before building the request. File inputs support a single file; the consuming application must validate and authorize uploads.

Validation failures keep user values. Network and HTTP failures show generic messages without exposing response details and keep the values available for retry. Inertia's finish callback releases the processing lock after success, validation errors, network errors, HTTP errors, or cancellation.
