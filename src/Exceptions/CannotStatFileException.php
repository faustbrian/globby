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
 * Exception thrown when file metadata cannot be retrieved.
 *
 * This exception is raised when the stat() system call fails to retrieve file
 * system metadata (size, permissions, timestamps, etc.) for a given path. This
 * typically occurs due to permission issues, I/O errors, or when the path exists
 * but is inaccessible to the current process.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class CannotStatFileException extends RuntimeException implements GlobbyException
{
    /**
     * Create an exception instance for a file that cannot be stat'd.
     *
     * Factory method that creates a new exception with a standardized error
     * message identifying the path where the stat operation failed.
     *
     * @param  string $path Absolute or relative path where stat() operation failed
     * @return self   New exception instance with formatted error message
     */
    public static function forPath(string $path): self
    {
        return new self('Cannot stat file: '.$path);
    }
}
