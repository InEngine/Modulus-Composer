<?php

namespace InEngine\ModulusComposer\Commands;

use Illuminate\Console\Command;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use Throwable;

/** Artisan entry for {@see ComposerModuleClient::update()}. */
class UpdateModuleCommand extends Command
{
    protected $signature = 'modulus-composer:update
        {package? : Composer package name (omit to update all tracked modules after composer update)}
        {--with-dependencies : Pass through to composer when updating a single package}';

    protected $description = 'Composer update module(s), then Modulus upgrade/publish hooks';

    public function handle(ComposerModuleClient $client): int
    {
        $package = $this->argument('package');
        $package = is_string($package) && $package !== '' ? $package : null;
        $withDeps = (bool) $this->option('with-dependencies');

        try {
            $touched = $client->update($package, $withDeps);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $count = count($touched);
        $this->info("Composer update finished. Modulus hooks ran for {$count} package(s).");

        foreach ($touched as $name) {
            $this->line(" - {$name}");
        }

        return self::SUCCESS;
    }
}
