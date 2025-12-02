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
 * Exception thrown when an empty glob pattern is provided.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InvalidPatternException extends InvalidArgumentException implements GlobbyException
{
    /**
     * Create exception for empty pattern.
     *
     * @return self Exception instance
     */
    public static function empty(): self
    {
        return new self('Glob pattern cannot be empty.');
    }
}
