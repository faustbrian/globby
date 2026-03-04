<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Exceptions;

use InvalidArgumentException;

use function sprintf;

/**
 * Exception thrown when a pattern argument has an incorrect data type.
 *
 * This exception is raised when Globby receives a pattern parameter that is not
 * a string or an array of strings. The library expects patterns to be either a
 * single glob pattern string or an array of multiple pattern strings for matching
 * against multiple patterns simultaneously.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidPatternTypeException extends InvalidArgumentException implements GlobbyException
{
    /**
     * Create an exception instance for an invalid pattern type.
     *
     * Factory method that creates a new exception with a descriptive error message
     * indicating the expected types (string or array of strings) and the actual
     * type that was provided.
     *
     * @param  string $type PHP type name of the invalid value received (e.g., "object", "integer")
     * @return self   New exception instance with formatted error message
     */
    public static function forType(string $type): self
    {
        return new self(sprintf('Expected string or array of strings for pattern, got %s.', $type));
    }
}
