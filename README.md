# InEngine ModulusComposer

Composer client extension for [`inengine/modulus`](https://github.com/InEngine/Modulus). Owns **require / remove / update**, host constraint rewrites (`~x.y.z` by default), and the real `ModuleUpdateApplier` so `modulus:update --auto` can apply patches.

**Not** a feature module — always-on when installed. Core hosts should **not** hard-require this package; opt in via Composer (path / Packagist).

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

Private Packagist / Satis / VCS: configure Composer `repositories` + `auth.json` as usual. This package shells the `composer` binary; it does not store credentials.

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
