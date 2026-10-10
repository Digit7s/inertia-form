# Changelog

Notable changes are documented here. Dates use ISO 8601. The v2.0.0 entry describes the current release candidate and is not a published release.

## Unreleased — planned v2.0.0

### Breaking changes

- Relationship-backed select options require explicit options or a constrained, limited callback; unbounded implicit relationship queries were removed.
- Sensitive model values are excluded from automatic form defaults unless explicitly opted in.
- The unused generator `--model` / `-m` option was removed.
- Dotted field names now submit as nested request data.
- Vue components were replaced with portable native Vue controls and are published under `resources/js/InertiaForm/components`.
- Form identity changes now preserve dirty values and pause submission until the new record is adopted or the old edits are discarded.
- File-bearing PUT/PATCH/DELETE requests use POST method spoofing.
- Rich-text field fallback is plain text; HTML is not rendered by the package.
- Laravel 11 support was removed because its upstream security-fix period ended in March 2026 and the Composer security policy blocks its available package set. PHP 8.2 remains supported with Laravel 12.

### Added

- Laravel 13 is included in the package's declared Illuminate constraints, pending the release compatibility matrix.
- Form lifecycle props and exposed reset/submit controls, field error focus, upload progress, nested fields, and native component implementations.
- Reproducible frontend and Laravel integration quality gates are being prepared.

### Fixed

- Relationship option resolution retains Eloquent relation constraints and applies a maximum option count.
- Package Vue files no longer depend on undocumented host UI aliases or icon libraries.
- Successful create redirects can adopt the new record identity without trapping the form in a dirty state.

## v1.0.0 — 2026-05-02

- Initial Packagist release. See the [v1.0.0 Git tag](https://github.com/Digit7s/inertia-form/tree/v1.0.0) for its source and API.
