<?php

namespace InEngine\ModulusComposer\Tests\Support;

use InEngine\ModulusComposer\Contracts\ComposerProcessRunner;
use InEngine\ModulusComposer\Exceptions\ComposerProcessFailedException;

final class RecordingComposerRunner implements ComposerProcessRunner
{
    /** @var list<list<string>> */
    public array $calls = [];

    /** @var list<string>|null */
    public ?array $failOn = null;

    public function __construct(
        public string $output = '',
    ) {}

    /**
     * {@inheritdoc}
     */
    public function run(array $arguments): string
    {
        $this->calls[] = $arguments;

        if ($this->failOn !== null && $arguments === $this->failOn) {
            throw new ComposerProcessFailedException(
                command: array_merge(['composer'], $arguments),
                exitCode: 1,
                stdout: '',
                stderr: 'simulated failure',
            );
        }

        return $this->output;
    }
}
