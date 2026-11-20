<?php

/**
 * Pest bootstrap: binds {@see TestCase} to every test in this directory.
 */

use InEngine\ModulusComposer\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
