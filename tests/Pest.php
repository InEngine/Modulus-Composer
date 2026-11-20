<?php

/**
 * Pest bootstrap: binds {@see \InEngine\ModulusComposer\Tests\TestCase} to every test in this directory.
 */

use InEngine\ModulusComposer\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
