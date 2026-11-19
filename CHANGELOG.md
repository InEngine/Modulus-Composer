# Changelog

All notable changes to **Modulus Composer** (`inengine/modulus-composer`) will be documented in this file.

## Unreleased

### Step 10 (initial)

- `ComposerModuleClient` for require / remove / update / approve
- `RootConstraintRewriter` using Modulus `UpgradeConstraintPolicy` (`~x.y.z`)
- `ComposerModuleUpdateApplier` replaces Modulus `NullModuleUpdateApplier`
- Artisan: `modulus-composer:require|remove|update|approve`
- Hooks: discover + publish after acquire; forget before remove; never auto-enable
- Module tooling: `.githooks`, attribution/session Cursor rules, `AGENTS.md`, PHPStan/Pint CI
- Pest coverage for client, hooks, runner, rewriter, applier, and Artisan commands
