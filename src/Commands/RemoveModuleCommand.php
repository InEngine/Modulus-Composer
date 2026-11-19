<?php

namespace InEngine\ModulusComposer\Commands;

use Illuminate\Console\Command;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use Throwable;

/** Artisan entry for {@see ComposerModuleClient::remove()}. */
class RemoveModuleCommand extends Command
{
    protected $signature = 'modulus-composer:remove
        {package : Composer package name}';

    protected $description = 'Forget from Modulus state, then composer remove (non-destructive of DB)';

    public function handle(ComposerModuleClient $client): int
    {
        $package = (string) $this->argument('package');

        try {
            $client->remove($package);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Removed [{$package}] from Composer and Modulus state.");

        return self::SUCCESS;
    }
}
