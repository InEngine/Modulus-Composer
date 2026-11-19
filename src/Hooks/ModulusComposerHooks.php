<?php

namespace InEngine\ModulusComposer\Hooks;

use InEngine\Modulus\ModuleRepository;

/**
 * Post/pre Composer lifecycle against Modulus (install ≠ enable).
 */
final class ModulusComposerHooks
{
    public function __construct(
        protected ModuleRepository $modules,
    ) {}

    /**
     * After require / update: discover (disabled) + publish assets/config.
     */
    public function afterAcquire(string $package): void
    {
        $this->modules->discover();
        $this->modules->publish($package);
    }

    /**
     * After update of an already-tracked/enabled package: upgrade + publish.
     */
    public function afterUpdate(string $package): void
    {
        if ($this->modules->get($package) === null) {
            $this->afterAcquire($package);

            return;
        }

        $this->modules->upgrade($package, publish: true);
    }

    /**
     * Before composer remove: drop Modulus state (hooks) while vendor still exists.
     */
    public function beforeRemove(string $package): void
    {
        if ($this->modules->get($package) !== null) {
            $this->modules->forget($package);
        }
    }
}
