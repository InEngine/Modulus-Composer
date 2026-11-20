<?php

namespace InEngine\ModulusComposer\Composer;

use Composer\InstalledVersions;
use InEngine\Modulus\ModuleRepository;
use InEngine\ModulusComposer\Contracts\ComposerProcessRunner;
use InEngine\ModulusComposer\Hooks\ModulusComposerHooks;

/**
 * Host Composer I/O for Modulus modules.
 *
 * Flow: shell `composer` via {@see ComposerProcessRunner}, then Modulus hooks
 * ({@see ModulusComposerHooks}). Install ≠ enable unless `$enable` / approve path.
 */
final class ComposerModuleClient
{
    public function __construct(
        protected ComposerProcessRunner $runner,
        protected RootConstraintRewriter $constraints,
        protected ModulusComposerHooks $hooks,
        protected ModuleRepository $modules,
    ) {}

    /**
     * composer require, then discover + publish. Enable only when $enable is true.
     */
    public function require(string $package, ?string $constraint = null, bool $enable = false): void
    {
        $package = $this->normalizePackage($package);
        $target = $constraint !== null && $constraint !== ''
            ? $package.':'.$constraint
            : $package;

        $this->runner->run(['require', $target]);
        $this->reloadInstalledVersions();

        if ($constraint === null || $constraint === '') {
            $installed = $this->installedPrettyVersion($package);

            if ($installed !== null) {
                $this->constraints->setSuggested($package, $installed);
            }
        }

        $this->hooks->afterAcquire($package);

        if ($enable) {
            $this->modules->enable($package);
        }
    }

    /**
     * Forget from Modulus, then composer remove.
     */
    public function remove(string $package): void
    {
        $package = $this->normalizePackage($package);
        $this->hooks->beforeRemove($package);
        $this->runner->run(['remove', $package]);
    }

    /**
     * composer update [package], then Modulus upgrade/publish.
     *
     * @return list<string> Packages that received Modulus afterUpdate hooks
     */
    public function update(?string $package = null, bool $withDependencies = false): array
    {
        $args = ['update'];

        if (is_string($package) && $package !== '') {
            $package = $this->normalizePackage($package);
            $args[] = $package;

            if ($withDependencies) {
                $args[] = '--with-dependencies';
            }
        }

        $this->runner->run($args);
        $this->reloadInstalledVersions();

        if (is_string($package) && $package !== '') {
            $this->hooks->afterUpdate($package);

            return [$package];
        }

        $touched = [];

        foreach (array_keys($this->modules->all()) as $tracked) {
            $this->hooks->afterUpdate($tracked);
            $touched[] = $tracked;
        }

        return $touched;
    }

    /**
     * Approve a minor/major bump: rewrite constraint to policy suggest, update, upgrade/publish.
     *
     * @return string The constraint written to the host composer.json (e.g. ~2.0.0)
     */
    public function approve(string $package, string $version): string
    {
        $package = $this->normalizePackage($package);
        $version = ltrim($version, 'v');
        $constraint = $this->constraints->setSuggested($package, $version);

        $this->runner->run(['update', $package]);
        $this->reloadInstalledVersions();
        $this->hooks->afterUpdate($package);

        return $constraint;
    }

    /**
     * Apply patch updates (used by ModuleUpdateApplier).
     *
     * @param  list<string>  $packages
     * @return list<string>
     */
    public function updatePackages(array $packages): array
    {
        $applied = [];

        foreach ($packages as $package) {
            $package = $this->normalizePackage($package);

            try {
                $this->runner->run(['update', $package]);
                $applied[] = $package;
            } catch (\Throwable) {
                // Skip failed packages; caller reports applied list.
            }
        }

        if ($applied !== []) {
            $this->reloadInstalledVersions();
        }

        return $applied;
    }

    /**
     * Canonicalize a Composer package name (trimmed, lowercase).
     */
    protected function normalizePackage(string $package): string
    {
        return strtolower(trim($package));
    }

    /**
     * Installed version of the package from Composer metadata, without a leading `v`.
     *
     * @return string|null Null when Composer metadata is unavailable or the package is not installed
     */
    protected function installedPrettyVersion(string $package): ?string
    {
        if (! class_exists(InstalledVersions::class) || ! InstalledVersions::isInstalled($package)) {
            return null;
        }

        $pretty = InstalledVersions::getPrettyVersion($package);

        return is_string($pretty) && $pretty !== '' ? ltrim($pretty, 'v') : null;
    }

    /**
     * Refresh Composer InstalledVersions after a subprocess mutated vendor/.
     */
    protected function reloadInstalledVersions(): void
    {
        if (! class_exists(InstalledVersions::class)) {
            return;
        }

        $cwd = config('modulus-composer.working_directory', '');
        $cwd = is_string($cwd) && $cwd !== '' ? $cwd : base_path();
        $installedPhp = $cwd.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'composer'.DIRECTORY_SEPARATOR.'installed.php';

        if (! is_file($installedPhp)) {
            return;
        }

        /** @var array<string, mixed> $data */
        $data = require $installedPhp;
        InstalledVersions::reload($data);
    }
}
