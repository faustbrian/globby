<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid or empty glob pattern is provided.
 *
 * This exception is raised when Globby receives a pattern that cannot be processed,
 * such as an empty string or whitespace-only pattern. Valid glob patterns must
 * contain at least one meaningful character to define what files to match.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidPatternException extends InvalidArgumentException implements GlobbyException
{
    /**
     * Create an exception instance for an empty pattern.
     *
     * Factory method that creates a new exception when the provided glob pattern
     * is empty or contains only whitespace, which is not a valid pattern for
     * file matching operations.
     *
     * @return self New exception instance with standardized error message
     */
    public static function empty(): self
    {
        return new self('Glob pattern cannot be empty.');
    }
}
