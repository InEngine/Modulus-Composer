<?php

namespace InEngine\ModulusComposer\Commands;

use Illuminate\Console\Command;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use Throwable;

/**
 * Artisan entry for {@see ComposerModuleClient::require()}.
 */
class RequireModuleCommand extends Command
{
    protected $signature = 'modulus-composer:require
        {package : Composer package name (e.g. inengine/alert)}
        {constraint? : Optional version constraint (omit to require latest then pin ~x.y.z)}
        {--enable : Also enable the module after publish (install ≠ enable by default)}';

    protected $description = 'Composer require a module, discover + publish (disabled unless --enable)';

    /** Execute the console command. */
    public function handle(ComposerModuleClient $client): int
    {
        $package = (string) $this->argument('package');
        $constraint = $this->argument('constraint');
        $constraint = is_string($constraint) && $constraint !== '' ? $constraint : null;
        $enable = (bool) $this->option('enable');

        try {
            $client->require($package, $constraint, $enable);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info($enable
            ? "Required, published, and enabled [{$package}]."
            : "Required and published [{$package}] (disabled). Enable with: php artisan modulus:enable {$package}");

        return self::SUCCESS;
    }
}
