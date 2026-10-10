# Contributing

Thanks for helping improve Inertia Form. Bug reports should include the package version, PHP/Laravel/Inertia/Vue versions, a small reproduction, and whether the issue is in PHP serialization, published Vue components, or host application behavior.

## Development setup

Use PHP 8.2 or newer, Composer 2, Node.js 20.19+ or 22.12+, and npm. Install the repository's PHP and frontend development dependencies:

```sh
composer install
npm ci
```

The package is a Composer library; its ignored local `composer.lock` is not part of a release. CI resolves clean dependency graphs for each compatibility row.

## Quality checks

```sh
composer validate --strict --no-check-lock
composer test
composer lint
npm test
npm run typecheck
npm run build
bash scripts/ci/clean-consumer-smoke.sh
git diff --check
```

The PHP matrix tests Laravel 11, 12, and 13 using compatible Testbench and Pest versions. The smoke script installs the current checkout through a local Composer path repository into a fresh temporary Laravel host, discovers the provider, publishes the Vue components, and builds the host frontend.

## Pull requests

- Keep changes focused and add tests for behavior changes.
- Do not include application secrets, generated `vendor/` or `node_modules/`, or consumer projects.
- Document changes to public PHP or Vue APIs in `CHANGELOG.md` and `UPGRADE.md`.
- Do not change a package version for local test convenience.
- Do not publish a release or tag from a pull request.
