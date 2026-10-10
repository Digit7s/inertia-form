# Relationship options and authorization

`Select::relationship()` describes the model relationship and the option label column. It does not authorize the user's eventual selection. `relationshipOptions()` is required to load relationship-backed options unless the field also has explicit `options()`.

```php
Select::make('project_id')
    ->relationship('projects', 'name')
    ->relationshipOptions(fn ($query) => $query
        ->where('projects.account_id', auth()->user()->account_id)
        ->where('projects.archived', false)
        ->orderBy('projects.name'), limit: 100);
```

The callback receives the configured relation's Eloquent builder. Return that same builder after applying constraints so its relation constraints and model scopes remain in place. The package caps result sets at 100 by default; callers may choose a limit from 1 to 500. Oversized result sets fail with a field-specific error rather than sending an unbounded list to the browser. For large or paginated relations, build a dedicated remote-search endpoint in the host application.

Use a policy or Gate to authorize the form page, and validate the submitted `project_id` with a tenant-scoped rule or a FormRequest callback. A browser can submit any identifier whether or not it appeared in the option list. Model global scopes do not replace an explicit application authorization decision.

For static or already-authorized values, use `options([...])` and omit `relationship()`. An explicit empty options list is valid and prevents automatic loading.
