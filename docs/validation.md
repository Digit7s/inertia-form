# Validation and authorization

Inertia Form renders fields and submits browser values. It does not create Laravel rules, validate requests, authorize users, authorize submitted relationship IDs, or persist models. Treat every client value as untrusted.

## Validate with a FormRequest

```php
use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:120'],
        ];
    }
}
```

Use only validated data for persistence. Authorize the model and any submitted foreign keys against the current user or tenant. `required()` and HTML input constraints improve browser usability; they do not replace these rules.

## Error display and preservation

Laravel's normal Inertia validation redirect is converted by the adapter into the error props consumed by `useForm`. Inertia Form displays the matching error, including dotted keys such as `address.city`, clears that field's error when it changes, and preserves submitted values after validation failure. Use `error-bag` when multiple forms can receive errors with the same names.

The clean consumer fixture exercises required and nested errors through the real Inertia Laravel adapter and FormRequest. The server remains responsible for choosing the correct redirect and response.

## Rich-text values

Text entered in the rich-text fallback is untrusted text. If an application adds an HTML editor, validate allowed content and sanitize it on the server before saving. Escape it on display or use a renderer with a maintained sanitizer and explicit allowlist. Do not render unsanitized values using `v-html`.
