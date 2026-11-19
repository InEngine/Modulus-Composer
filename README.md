# Modulus Composer

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/InEngine/ModulusComposer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/InEngine/ModulusComposer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![CC BY-NC-SA 4.0](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-lightgrey.svg)](http://creativecommons.org/licenses/by-nc-sa/4.0/)

**Modulus Composer** (`inengine/modulus-composer`) is the Composer client extension for [Modulus](https://github.com/InEngine/Modulus).

It owns **require / remove / update**, host constraint rewrites (default `~x.y.z`), and the real `ModuleUpdateApplier` so `modulus:update --auto` can apply patches. Modulus core stays free of Composer shelling.

**Not** a feature module — always-on when installed. Hosts (including InEngine Core) should **not** hard-require this package; opt in via Composer path / Packagist.

Docs live with Modulus: [Modulus Composer](https://github.com/InEngine/Modulus/blob/feat/modulus-state-store/docs/modulus-composer.md) · [docs/](https://github.com/InEngine/Modulus/tree/feat/modulus-state-store/docs).

## Install ≠ enable

```bash
php artisan modulus-composer:require inengine/alert
# → composer require → Modulus::discover() → Modulus::publish()
# Module stays disabled until:
php artisan modulus:enable inengine/alert
```

Use `--enable` only when you intentionally want enable in the same step.

## Commands

| Command | Role |
|---------|------|
| `modulus-composer:require {package} {constraint?} {--enable}` | Acquire + publish |
| `modulus-composer:remove {package}` | `Modulus::forget` then `composer remove` |
| `modulus-composer:update {package?}` | `composer update` + Modulus upgrade/publish |
| `modulus-composer:approve {package} {version}` | Rewrite constraint under policy, update, upgrade |

Private Packagist / Satis / VCS: configure Composer `repositories` + `auth.json` as usual. Modulus Composer shells the `composer` binary; it does not store credentials.

## Constraint policy

Uses `modulus.upgrades.default_constraint` (default **patch** → `~x.y.z`). Minor/major bumps need `modulus-composer:approve` (or UI later). Optional auto-patch: `modulus.updates.auto_patch=true` once this package rebinds `ModuleUpdateApplier`.

## Local path (monorepo)

```json
{
  "require": {
    "inengine/modulus-composer": "*@dev"
  }
}
```

With Core `composer.local.json` path repos under `../Modules/*`.

## Quality

```bash
composer test
composer analyse
composer format
```

## License

CC BY-NC-SA 4.0. See [LICENSE.txt](LICENSE.txt).
