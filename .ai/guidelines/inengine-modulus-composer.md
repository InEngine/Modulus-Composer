# InEngine ModulusComposer (package repo)

This repository is `inengine/modulus-composer` (Spatie package + Orchestra Testbench), not a full Laravel app.

## Commands

- `vendor/bin/pest` — run tests
- `vendor/bin/phpstan analyse --memory-limit=512M` — static analysis
- `vendor/bin/pint` — format PHP
- Prefer Testbench / `composer test` scripts in `composer.json`

## Agent rules

- Read `README.md` and Modulus `DESIGN.md` Step 10 before changing Composer I/O or hooks.
- Update `CHANGELOG.md` Unreleased when behavior changes.
- Keep install ≠ enable: never auto-enable on require/update.
- Prefer PHPDoc blocks over inline comments.
- Do not add marketplace or operator UI here.
- Git author: James Johnson / excellentingenuity; use `.githooks` (no Cursor co-author trailers).
