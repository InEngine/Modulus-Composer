<?php

use InEngine\Modulus\ModuleRepository;
use InEngine\ModulusComposer\Hooks\ModulusComposerHooks;

it('afterAcquire discovers then publishes and never enables', function () {
    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('discover')->once()->andReturn(1);
    $modules->shouldReceive('publish')->once()->with('inengine/alert');
    $modules->shouldNotReceive('enable');

    (new ModulusComposerHooks($modules))->afterAcquire('inengine/alert');
});

it('afterUpdate upgrades with publish when the package is already tracked', function () {
    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn([
        'enabled' => true,
        'version' => '1.0.0',
    ]);
    $modules->shouldReceive('upgrade')->once()->with('inengine/alert', true)->andReturn(true);
    $modules->shouldNotReceive('discover');
    $modules->shouldNotReceive('publish');

    (new ModulusComposerHooks($modules))->afterUpdate('inengine/alert');
});

it('afterUpdate falls back to acquire hooks when the package is not tracked', function () {
    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn(null);
    $modules->shouldReceive('discover')->once()->andReturn(1);
    $modules->shouldReceive('publish')->once()->with('inengine/alert');

    (new ModulusComposerHooks($modules))->afterUpdate('inengine/alert');
});

it('beforeRemove forgets only when Modulus tracks the package', function () {
    $modules = Mockery::mock(ModuleRepository::class);
    $modules->shouldReceive('get')->once()->with('inengine/alert')->andReturn(['enabled' => false]);
    $modules->shouldReceive('forget')->once()->with('inengine/alert');

    (new ModulusComposerHooks($modules))->beforeRemove('inengine/alert');

    $modules2 = Mockery::mock(ModuleRepository::class);
    $modules2->shouldReceive('get')->once()->with('inengine/missing')->andReturn(null);
    $modules2->shouldNotReceive('forget');

    (new ModulusComposerHooks($modules2))->beforeRemove('inengine/missing');
});
