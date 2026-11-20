<?php

namespace InEngine\ModulusComposer\Exceptions;

use RuntimeException;

/**
 * Thrown when the host composer.json is missing, unreadable, malformed, or cannot be written.
 */
final class InvalidComposerJsonException extends RuntimeException {}
