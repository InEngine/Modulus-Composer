<?php

namespace InEngine\ModulusComposer\Tests;

use InEngine\Modulus\ModulusServiceProvider;
use InEngine\ModulusComposer\ModulusComposerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base Orchestra Testbench case for the Modulus Composer package tests.
 *
 * Registers the Modulus and Modulus Composer service providers and isolates
 * Modulus state in a unique temporary file per test run.
 */
class TestCase extends Orchestra
{
    /**
     * Register the package service providers under test.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return list<class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            ModulusServiceProvider::class,
            ModulusComposerServiceProvider::class,
        ];
    }

    /**
     * Configure the testing environment: default database connection and
     * a temporary Modulus state file with upgrade/update defaults for tests.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $statePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'modulus-composer-testbench-'.uniqid('', true).'.json';
        config()->set('modulus.state_path', $statePath);
        config()->set('modulus.state_fsync', false);
        config()->set('modulus.upgrades.default_constraint', 'patch');
        config()->set('modulus.upgrades.auto_apply_installed', true);
        config()->set('modulus.updates.auto_patch', false);
    }
}
