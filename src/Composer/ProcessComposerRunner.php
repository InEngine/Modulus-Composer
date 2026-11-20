<?php

namespace InEngine\ModulusComposer\Composer;

use Illuminate\Support\Facades\Process;
use InEngine\ModulusComposer\Contracts\ComposerProcessRunner;
use InEngine\ModulusComposer\Exceptions\ComposerProcessFailedException;

/**
 * {@see ComposerProcessRunner} that shells out to the Composer binary via Laravel's Process facade.
 *
 * Appends non-interactive flags and throws {@see ComposerProcessFailedException} on non-zero exit.
 */
final class ProcessComposerRunner implements ComposerProcessRunner
{
    /**
     * @param  list<string>  $nonInteractiveFlags
     */
    public function __construct(
        protected string $binary = 'composer',
        protected string $workingDirectory = '',
        protected int $timeout = 600,
        protected array $nonInteractiveFlags = ['--no-interaction', '--no-ansi'],
    ) {
        if ($this->workingDirectory === '') {
            $this->workingDirectory = base_path();
        }
    }

    /**
     * Build a runner from the `modulus-composer` config (binary, working directory, timeout, flags).
     */
    public static function fromConfig(): self
    {
        $flags = config('modulus-composer.non_interactive', ['--no-interaction', '--no-ansi']);

        if (! is_array($flags)) {
            $flags = ['--no-interaction', '--no-ansi'];
        }

        /** @var list<string> $flagList */
        $flagList = array_values(array_filter($flags, 'is_string'));

        $cwd = config('modulus-composer.working_directory', '');
        $cwd = is_string($cwd) ? $cwd : '';

        $binary = config('modulus-composer.composer_binary', 'composer');
        $binary = is_string($binary) && $binary !== '' ? $binary : 'composer';

        $timeout = config('modulus-composer.timeout', 600);
        $timeout = is_numeric($timeout) ? (int) $timeout : 600;

        return new self(
            binary: $binary,
            workingDirectory: $cwd,
            timeout: $timeout,
            nonInteractiveFlags: $flagList,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function run(array $arguments): string
    {
        $command = array_merge([$this->binary], $arguments, $this->nonInteractiveFlags);

        $result = Process::path($this->workingDirectory)
            ->timeout($this->timeout)
            ->run($command);

        if (! $result->successful()) {
            throw new ComposerProcessFailedException(
                command: $command,
                exitCode: $result->exitCode() ?? 1,
                stdout: $result->output(),
                stderr: $result->errorOutput(),
            );
        }

        return $result->output();
    }
}
