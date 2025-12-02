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
 * Exception thrown when a file cannot be read.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class FileUnreadableException extends RuntimeException implements GlobbyException
{
    /**
     * Create exception for unreadable file.
     *
     * @param  string $path The file path that cannot be read
     * @return self   Exception instance
     */
    public static function forPath(string $path): self
    {
        return new self('Cannot read file: '.$path);
    }
}
