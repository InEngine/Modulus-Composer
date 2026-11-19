<?php

use InEngine\Modulus\ModuleRepository;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use InEngine\ModulusComposer\Composer\RootConstraintRewriter;
use InEngine\ModulusComposer\Exceptions\ComposerProcessFailedException;
use InEngine\ModulusComposer\Hooks\ModulusComposerHooks;
use InEngine\ModulusComposer\Tests\Support\RecordingComposerRunner;

it('requires with --enable after publish', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-enable-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => ['php' => '^8.5']], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('discover')->once()->andReturn(1);
    $modules->shouldReceive('publish')->once()->with('inengine/alert');
    $modules->shouldReceive('enable')->once()->with('inengine/alert');

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    $client->require('inengine/alert', '1.0.0', enable: true);

    expect($runner->calls)->toBe([['require', 'inengine/alert:1.0.0']]);

    unlink($path);
});

it('pins ~version when require has no explicit constraint and InstalledVersions is unavailable', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-pin-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => ['php' => '^8.5']], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('discover')->once()->andReturn(0);
    $modules->shouldReceive('publish')->once()->with('inengine/alert');

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    // No installed version in testbench → skip pin; still acquires.
    $client->require('inengine/alert');

    expect($runner->calls)->toBe([['require', 'inengine/alert']])
        ->and(json_decode((string) file_get_contents($path), true)['require'])->not->toHaveKey('inengine/alert');

    unlink($path);
});

it('updates a single package with --with-dependencies when requested', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-upd-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn(['enabled' => true]);
    $modules->shouldReceive('upgrade')->once()->with('inengine/alert', true)->andReturn(true);

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    expect($client->update('inengine/alert', withDependencies: true))->toBe(['inengine/alert'])
        ->and($runner->calls)->toBe([['update', 'inengine/alert', '--with-dependencies']]);

    unlink($path);
});

it('runs afterUpdate for every tracked package when updating all', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-upd-all-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('all')->once()->andReturn([
        'inengine/alert' => ['enabled' => true],
        'inengine/calendar' => ['enabled' => false],
    ]);
    $modules->shouldReceive('get')->with('inengine/alert')->andReturn(['enabled' => true]);
    $modules->shouldReceive('get')->with('inengine/calendar')->andReturn(['enabled' => false]);
    $modules->shouldReceive('upgrade')->twice()->andReturn(true);

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    expect($client->update(null))->toBe(['inengine/alert', 'inengine/calendar'])
        ->and($runner->calls)->toBe([['update']]);

    unlink($path);
});

it('updatePackages skips packages that fail composer update', function () {
    $runner = new RecordingComposerRunner;
    $runner->failOn = ['update', 'inengine/broken'];
    $path = sys_get_temp_dir().'/modulus-composer-skip-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    expect($client->updatePackages(['inengine/alert', 'inengine/broken', 'inengine/calendar']))
        ->toBe(['inengine/alert', 'inengine/calendar']);

    unlink($path);
});

it('remove skips forget when package is not tracked', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-rm-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn(null);
    $modules->shouldNotReceive('forget');

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

it('propagates composer failures from require', function () {
    $runner = new RecordingComposerRunner;
    $runner->failOn = ['require', 'inengine/alert:1.0.0'];
    $path = sys_get_temp_dir().'/modulus-composer-fail-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldNotReceive('discover');

    $client = new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    );

    expect(fn () => $client->require('inengine/alert', '1.0.0'))
        ->toThrow(ComposerProcessFailedException::class);

    unlink($path);
});
