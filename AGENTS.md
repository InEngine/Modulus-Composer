# Modulus Composer

Display name **Modulus Composer**. Composer package `inengine/modulus-composer` — optional Modulus **extension** for Composer I/O (PHP ^8.5, illuminate/contracts ^12||^13).

Depends on `inengine/modulus`. Hosts opt in; Core must **not** hard-require this package.

## Build and test

- `composer test` / `vendor/bin/pest`
- `composer analyse` / `vendor/bin/phpstan analyse --memory-limit=512M`
- `vendor/bin/pint --dirty`

## Project docs

- `README.md`, `CHANGELOG.md`
- Repo guidelines: `.ai/guidelines/inengine-modulus-composer.md`
- Cursor: `.cursor/rules/co-authored-by-jarvis.mdc`, `.cursor/rules/session-handoff.mdc`
- Session: `.cursor/SESSION_HANDOFF.md`

## Git attribution

- Author: **James Johnson** / **excellentingenuity**
- Enable hooks: `git config core.hooksPath .githooks`
- No `Co-authored-by: Cursor` trailers

<laravel-boost-guidelines>
=== foundation rules ===

# Package agent rules

- Follow existing conventions in sibling files when editing.
- Prefer PHPDoc blocks over inline comments unless logic is exceptionally complex.
- Update `CHANGELOG.md` Unreleased when behavior changes.
- This package owns Composer require/remove/update and constraint rewrite — never fold that into `inengine/modulus`.
- Do not add marketplace browse or operator UI here — those are separate `modulus-*` extensions.
- Be concise in replies.

=== inengine/modulus-composer rules ===

# inengine/modulus-composer

Composer client for Modulus-compatible modules.

## Install ≠ enable

1. `modulus-composer:require` → `composer require` → `Modulus::discover()` → `Modulus::publish()`.
2. Module stays **disabled** unless `--enable`.
3. `modulus-composer:remove` → `Modulus::forget()` **before** `composer remove`.
4. `modulus-composer:approve` rewrites host constraint under `modulus.upgrades.default_constraint` (`patch` → `~x.y.z`), then update + upgrade/publish.

## Bindings

- Rebinds `ModuleUpdateApplier` to `ComposerModuleUpdateApplier` (replaces Modulus `NullModuleUpdateApplier`).
- Shells the host `composer` binary via Laravel Process; private Packagist via Composer `auth.json`.

## Quality

- Pest, PHPStan, Pint. PHPDoc over inline comments.

</laravel-boost-guidelines>
