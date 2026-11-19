<?php

use InEngine\Modulus\ModuleRepository;
use InEngine\ModulusComposer\Composer\ComposerModuleClient;
use InEngine\ModulusComposer\Composer\RootConstraintRewriter;
use InEngine\ModulusComposer\Hooks\ModulusComposerHooks;
use InEngine\ModulusComposer\Tests\Support\RecordingComposerRunner;

it('modulus-composer:remove forgets then removes', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-cmd-rm-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn(['enabled' => false]);
    $modules->shouldReceive('forget')->once()->with('inengine/alert');

    app()->instance(ComposerModuleClient::class, new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    ));

    $this->artisan('modulus-composer:remove', ['package' => 'inengine/alert'])
        ->assertSuccessful();

    expect($runner->calls)->toBe([['remove', 'inengine/alert']]);

    unlink($path);
});

it('modulus-composer:update passes with-dependencies for a single package', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-cmd-upd-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => []], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn(['enabled' => true]);
    $modules->shouldReceive('upgrade')->once()->with('inengine/alert', true)->andReturn(true);

    app()->instance(ComposerModuleClient::class, new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    ));

    $this->artisan('modulus-composer:update', [
        'package' => 'inengine/alert',
        '--with-dependencies' => true,
    ])->assertSuccessful();

    expect($runner->calls)->toBe([['update', 'inengine/alert', '--with-dependencies']]);

    unlink($path);
});

it('modulus-composer:require --enable enables after publish', function () {
    $runner = new RecordingComposerRunner;
    $path = sys_get_temp_dir().'/modulus-composer-cmd-en-'.uniqid('', true).'.json';
    file_put_contents($path, json_encode(['name' => 'host/app', 'require' => ['php' => '^8.5']], JSON_PRETTY_PRINT));

    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('discover')->once()->andReturn(0);
    $modules->shouldReceive('publish')->once()->with('inengine/alert');
    $modules->shouldReceive('enable')->once()->with('inengine/alert');

    app()->instance(ComposerModuleClient::class, new ComposerModuleClient(
        $runner,
        new RootConstraintRewriter($path),
        new ModulusComposerHooks($modules),
        $modules,
    ));

    $this->artisan('modulus-composer:require', [
        'package' => 'inengine/alert',
        'constraint' => '1.2.3',
        '--enable' => true,
    ])->assertSuccessful();

    unlink($path);
});
