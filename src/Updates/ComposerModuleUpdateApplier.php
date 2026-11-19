<?php

namespace InEngine\ModulusComposer\Updates;

use InEngine\Modulus\Contracts\ModuleUpdateApplier;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;

/**
 * Real ModuleUpdateApplier: runs Composer update for auto-eligible packages.
 */
final class ComposerModuleUpdateApplier implements ModuleUpdateApplier
{
    public function __construct(
        protected ComposerModuleClient $client,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function apply(array $updates): array
    {
        $packages = [];

        foreach ($updates as $update) {
            if (! $update->autoEligible) {
                continue;
            }

            $packages[] = $update->package;
        }

        if ($packages === []) {
            return [];
        }

        return $this->client->updatePackages($packages);
    }
}
