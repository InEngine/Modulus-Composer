<?php

/**
 * Feature coverage for command registration and the approve/require `modulus-composer:*` Artisan commands.
 */

use Illuminate\Support\Facades\Artisan;
use InEngine\Modulus\ModuleRepository;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use InEngine\ModulusComposer\Composer\RootConstraintRewriter;
use InEngine\ModulusComposer\Contracts\ComposerProcessRunner;
use InEngine\ModulusComposer\Hooks\ModulusComposerHooks;
use InEngine\ModulusComposer\Tests\Support\RecordingComposerRunner;

it('registers modulus-composer artisan commands', function () {
    $commands = Artisan::all();

    expect($commands)->toHaveKeys([
        'modulus-composer:require',
        'modulus-composer:remove',
        'modulus-composer:update',
        'modulus-composer:approve',
    ]);
});

it('modulus-composer:approve rewrites constraint and updates via composer', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-feature-approve-'.uniqid('', true).'.json';
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

    app()->instance(ComposerProcessRunner::class, $runner);
    app()->instance(ComposerModuleClient::class, $client);

    $this->artisan('modulus-composer:approve', [
        'package' => 'inengine/alert',
        'version' => '2.0.0',
    ])->assertSuccessful();

    expect($runner->calls)->toBe([['update', 'inengine/alert']])
        ->and(json_decode((string) file_get_contents($path), true)['require']['inengine/alert'])->toBe('~2.0.0');

    unlink($path);
});

it('modulus-composer:require does not enable unless flagged', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-feature-require-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => ['php' => '^8.5']], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('discover')->once()->andReturn(0);
    $modules->shouldReceive('publish')->once()->with('inengine/alert');
    $modules->shouldNotReceive('enable');

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    app()->instance(ComposerModuleClient::class, $client);

    $this->artisan('modulus-composer:require', [
        'package' => 'inengine/alert',
        'constraint' => '1.2.3',
    ])->assertSuccessful();

    expect($runner->calls)->toBe([['require', 'inengine/alert:1.2.3']]);

    unlink($path);
});
