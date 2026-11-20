<?php

/**
 * Unit coverage for {@see ComposerModuleUpdateApplier}.
 */

use InEngine\Modulus\DataTransferObjects\AvailableModuleUpdate;
use InEngine\Modulus\ModuleRepository;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use InEngine\ModulusComposer\Composer\RootConstraintRewriter;
use InEngine\ModulusComposer\Hooks\ModulusComposerHooks;
use InEngine\ModulusComposer\Tests\Support\RecordingComposerRunner;
use InEngine\ModulusComposer\Updates\ComposerModuleUpdateApplier;

it('applies only auto-eligible updates via composer update', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-applier-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    $applier = new ComposerModuleUpdateApplier($client);

    $applied = $applier->apply([
        new AvailableModuleUpdate(
            package: 'inengine/alert',
            installedVersion: '1.0.0',
            latestVersion: '1.0.1',
            bump: AvailableModuleUpdate::BUMP_PATCH,
            withinConstraint: true,
            autoEligible: true,
        ),
        new AvailableModuleUpdate(
            package: 'inengine/calendar',
            installedVersion: '1.0.0',
            latestVersion: '2.0.0',
            bump: AvailableModuleUpdate::BUMP_MAJOR,
            withinConstraint: false,
            autoEligible: false,
        ),
    ]);

    expect($applied)->toBe(['inengine/alert'])
        ->and($runner->calls)->toBe([['update', 'inengine/alert']]);

    unlink($path);
});
