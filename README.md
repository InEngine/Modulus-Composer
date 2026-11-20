# Modulus Composer

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/InEngine/Modulus-Composer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/InEngine/Modulus-Composer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.txt)

**Modulus Composer** (`inengine/modulus-composer`) is the Composer client extension for [Modulus](https://github.com/InEngine/Modulus). **Step 10 — done.** License: **MIT**.

It owns **require / remove / update**, host constraint rewrites (default `~x.y.z`), and the real `ModuleUpdateApplier` so `modulus:update --auto` can apply patches. Modulus core stays free of Composer shelling.

**Not** a feature module — always-on when installed. InEngine Core hard-requires this package from Packagist (`^0.1@beta`). Operator UI is **`modulus-ui` (private Pro)** — separate package.

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

Published require: `inengine/modulus` `^0.1@beta`. For sibling path develop:

```bash
cp composer.local.dist.json composer.local.json   # gitignored; path + *@dev
composer update inengine/modulus
```

```bash
composer require inengine/modulus-composer:^0.1@beta
```

Or `*@dev` with Core `composer.local.json` path repos under `../Modules/*`.

## Quality

```bash
composer test
composer analyse
composer format
```

## License

The MIT License (MIT). See [LICENSE.txt](LICENSE.txt).
