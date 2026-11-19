<?php

use Illuminate\Support\Facades\Process;
use InEngine\ModulusComposer\Composer\ProcessComposerRunner;
use InEngine\ModulusComposer\Exceptions\ComposerProcessFailedException;

it('runs composer with non-interactive flags appended', function () {
    Process::fake([
        '*' => Process::result(output: 'ok'),
    ]);

    $runner = new ProcessComposerRunner(
        binary: 'composer',
        workingDirectory: base_path(),
        timeout: 30,
        nonInteractiveFlags: ['--no-interaction', '--no-ansi'],
    );

    expect(trim($runner->run(['outdated', '--direct'])))->toBe('ok');

    Process::assertRan(function ($process): bool {
        $command = $process->command;

        if (is_array($command)) {
            return $command === ['composer', 'outdated', '--direct', '--no-interaction', '--no-ansi'];
        }

        return str_contains((string) $command, 'outdated')
            && str_contains((string) $command, '--no-interaction');
    });
});

it('throws ComposerProcessFailedException when composer exits non-zero', function () {
    Process::fake([
        '*' => Process::result(output: '', errorOutput: 'boom', exitCode: 2),
    ]);

    $runner = new ProcessComposerRunner(
        binary: 'composer',
        workingDirectory: base_path(),
        timeout: 30,
        nonInteractiveFlags: [],
    );

    $runner->run(['require', 'inengine/alert']);
})->throws(ComposerProcessFailedException::class);
