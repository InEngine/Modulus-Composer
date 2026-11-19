<?php

namespace InEngine\ModulusComposer\Commands;

use Illuminate\Console\Command;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use Throwable;

/** Artisan entry for {@see ComposerModuleClient::approve()}. */
class ApproveModuleCommand extends Command
{
    protected $signature = 'modulus-composer:approve
        {package : Composer package name}
        {version : Target version to approve (minor/major bump)}';

    protected $description = 'Rewrite host constraint (~x.y.z policy), composer update, Modulus upgrade';

    public function handle(ComposerModuleClient $client): int
    {
        $package = (string) $this->argument('package');
        $version = (string) $this->argument('version');

        try {
            $constraint = $client->approve($package, $version);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Approved [{$package}] → {$version}; host constraint is now [{$constraint}].");

        return self::SUCCESS;
    }
}
