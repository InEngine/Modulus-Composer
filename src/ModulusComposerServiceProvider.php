<?php

namespace InEngine\ModulusComposer;

use InEngine\Modulus\Contracts\ModuleUpdateApplier;
use InEngine\Modulus\ModuleRepository;
use InEngine\Modulus\Updates\NullModuleUpdateApplier;
use InEngine\Modulus\Upgrades\UpgradeConstraintPolicy;
use InEngine\ModulusComposer\Commands\ApproveModuleCommand;
use InEngine\ModulusComposer\Commands\RemoveModuleCommand;
use InEngine\ModulusComposer\Commands\RequireModuleCommand;
use InEngine\ModulusComposer\Commands\UpdateModuleCommand;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use InEngine\ModulusComposer\Composer\ProcessComposerRunner;
use InEngine\ModulusComposer\Composer\RootConstraintRewriter;
use InEngine\ModulusComposer\Contracts\ComposerProcessRunner;
use InEngine\ModulusComposer\Hooks\ModulusComposerHooks;
use InEngine\ModulusComposer\Updates\ComposerModuleUpdateApplier;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Always-on Modulus extension (not a feature module).
 *
 * Registers Artisan Composer I/O commands and rebinds
 * {@see ModuleUpdateApplier} so `modulus:update --auto` can apply patches.
 */
class ModulusComposerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('modulus-composer')
            ->hasConfigFile('modulus-composer')
            ->hasCommands([
                RequireModuleCommand::class,
                RemoveModuleCommand::class,
                UpdateModuleCommand::class,
                ApproveModuleCommand::class,
            ]);
    }

    /**
     * Bind Composer runner/client and replace Modulus {@see NullModuleUpdateApplier}.
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(ComposerProcessRunner::class, function (): ComposerProcessRunner {
            return ProcessComposerRunner::fromConfig();
        });

        $this->app->singleton(RootConstraintRewriter::class, function ($app): RootConstraintRewriter {
            return RootConstraintRewriter::fromConfig(
                UpgradeConstraintPolicy::fromConfig(
                    $app['config']->get('modulus.upgrades.default_constraint', 'patch')
                )
            );
        });

        $this->app->singleton(ModulusComposerHooks::class, function ($app): ModulusComposerHooks {
            return new ModulusComposerHooks($app->make(ModuleRepository::class));
        });

        $this->app->singleton(ComposerModuleClient::class, function ($app): ComposerModuleClient {
            return new ComposerModuleClient(
                $app->make(ComposerProcessRunner::class),
                $app->make(RootConstraintRewriter::class),
                $app->make(ModulusComposerHooks::class),
                $app->make(ModuleRepository::class),
            );
        });

        // Override Modulus NullModuleUpdateApplier when this extension is installed.
        $this->app->singleton(ModuleUpdateApplier::class, function ($app): ModuleUpdateApplier {
            return new ComposerModuleUpdateApplier($app->make(ComposerModuleClient::class));
        });
    }
}
