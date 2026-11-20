<?php

/**
 * Unit coverage for the core flows of {@see \InEngine\ModulusComposer\Composer\ComposerModuleClient} (require, remove, approve) and update-applier binding.
 */

use InEngine\Modulus\Contracts\ModuleUpdateApplier;
use InEngine\Modulus\ModuleRepository;
use InEngine\Modulus\Updates\NullModuleUpdateApplier;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use InEngine\ModulusComposer\Composer\RootConstraintRewriter;
use InEngine\ModulusComposer\Hooks\ModulusComposerHooks;
use InEngine\ModulusComposer\Tests\Support\RecordingComposerRunner;
use InEngine\ModulusComposer\Updates\ComposerModuleUpdateApplier;

it('requires a package, discovers and publishes, and does not enable by default', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-require-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => ['php' => '^8.5']], JSON_PRETTY_PRINT));
    $constraints = new RootConstraintRewriter($path);

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('discover')->once()->andReturn(1);
    $modules->shouldReceive('publish')->once()->with('inengine/alert');
    $modules->shouldNotReceive('enable');

    $client = new ComposerModuleClient(
        $runner,
        $constraints,
        new ModulusComposerHooks($modules),
        $modules,
    );

    $client->require('InEngine/Alert', '1.2.3');

    expect($runner->calls)->toBe([['require', 'inengine/alert:1.2.3']]);

    unlink($path);
});

it('forgets from Modulus before composer remove', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-remove-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn(['enabled' => false]);
    $modules->shouldReceive('forget')->once()->with('inengine/alert');

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    $client->remove('inengine/alert');

    expect($runner->calls)->toBe([['remove', 'inengine/alert']]);

    unlink($path);
});

it('approves a bump by rewriting constraint then updating', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-approve-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode([
        'name' => 'host/app',
        'require' => ['inengine/alert' => '~1.0.0'],
    ], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn(['enabled' => true, 'version' => '1.0.0']);
    $modules->shouldReceive('upgrade')->once()->with('inengine/alert', true)->andReturn(true);

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    $constraint = $client->approve('inengine/alert', '2.0.0');

    expect($constraint)->toBe('~2.0.0')
        ->and($runner->calls)->toBe([['update', 'inengine/alert']])
        ->and(json_decode((string) file_get_contents($path), true)['require']['inengine/alert'])->toBe('~2.0.0');

    unlink($path);
});

it('binds ComposerModuleUpdateApplier instead of NullModuleUpdateApplier', function () {
    $applier = app(ModuleUpdateApplier::class);

    expect($applier)->toBeInstanceOf(ComposerModuleUpdateApplier::class)
        ->and($applier)->not->toBeInstanceOf(NullModuleUpdateApplier::class);
});
