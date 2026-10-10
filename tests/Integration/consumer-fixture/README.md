# Clean consumer integration fixture

These focused application files are copied into a fresh Laravel 13 skeleton by `scripts/ci/clean-consumer-smoke.sh`. They exercise the package with the real Inertia Laravel adapter, a Laravel FormRequest, SQLite, fake file storage, and a browser test using the real Inertia Vue adapter. The skeleton, Composer vendor tree, database, and frontend build output remain temporary and are not checked in.

The form and controller are test fixtures, not production application code. In particular, the request's `authorize()` method is permissive for the disposable smoke app; consumers must implement their own authorization.
