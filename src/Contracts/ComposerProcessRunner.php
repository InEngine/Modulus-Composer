<?php

namespace InEngine\ModulusComposer\Contracts;

use InEngine\ModulusComposer\Exceptions\ComposerProcessFailedException;

interface ComposerProcessRunner
{
    /**
     * Run a Composer CLI command in the configured working directory.
     *
     * @param  list<string>  $arguments  Arguments after the binary (e.g. ['require', 'vendor/pkg'])
     * @return string Combined stdout on success
     *
     * @throws ComposerProcessFailedException
     */
    public function run(array $arguments): string;
}
