<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Exceptions;

use RuntimeException;

/**
 * Exception thrown when file stats cannot be retrieved.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class CannotStatFileException extends RuntimeException implements GlobbyException
{
    /**
     * Create exception for file that cannot be stat'd.
     *
     * @param  string $path The file path
     * @return self   Exception instance
     */
    public static function forPath(string $path): self
    {
        return new self('Cannot stat file: '.$path);
    }
}
