<?php

namespace InEngine\ModulusComposer\Exceptions;

use RuntimeException;

/**
 * Thrown when a Composer subprocess exits non-zero.
 *
 * Exposes the command, exit code, and captured output; the message prefers stderr over stdout.
 */
final class ComposerProcessFailedException extends RuntimeException
{
    /**
     * @param  list<string>  $command
     */
    public function __construct(
        public readonly array $command,
        public readonly int $exitCode,
        public readonly string $stdout,
        public readonly string $stderr,
    ) {
        $joined = implode(' ', $command);
        $detail = trim($stderr !== '' ? $stderr : $stdout);

        parent::__construct(
            $detail !== ''
                ? "Composer command failed ({$exitCode}): {$joined}\n{$detail}"
                : "Composer command failed ({$exitCode}): {$joined}"
        );
    }
}
