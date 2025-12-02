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
 * Exception thrown when an invalid pattern type is provided.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidPatternTypeException extends InvalidArgumentException implements GlobbyException
{
    /**
     * Create exception for invalid pattern type.
     *
     * @param  string $type The actual type received
     * @return self   Exception instance
     */
    public static function forType(string $type): self
    {
        return new self(sprintf('Expected string or array of strings for pattern, got %s.', $type));
    }
}
