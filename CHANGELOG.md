# Changelog

All notable changes to **Modulus Composer** (`inengine/modulus-composer`) will be documented in this file.

## Unreleased

## 0.1.0-beta.2 - 2026-11-20

### Changed

- Require `inengine/modulus` `^0.1@beta || *@dev` so Packagist CI and local path develops both resolve

## 0.1.0-beta.1 - 2026-11-20

### Added

- First public beta on Packagist / GitHub Releases

### Changed

- Relicense as MIT
- Packagist-ready shape: path develop via gitignored `composer.local.json` (no path repos in published `composer.json`)
- Require `inengine/modulus` `^0.1@beta` (path `*@dev` only via `composer.local.json`)

### Step 10 (initial)

- `ComposerModuleClient` for require / remove / update / approve
- `RootConstraintRewriter` using Modulus `UpgradeConstraintPolicy` (`~x.y.z`)
- `ComposerModuleUpdateApplier` replaces Modulus `NullModuleUpdateApplier`
- Artisan: `modulus-composer:require|remove|update|approve`
- Hooks: discover + publish after acquire; forget before remove; never auto-enable
- Module tooling: `.githooks`, attribution/session Cursor rules, `AGENTS.md`, PHPStan/Pint CI
- Pest coverage for client, hooks, runner, rewriter, applier, and Artisan commands
