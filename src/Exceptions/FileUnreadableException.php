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
 * Exception thrown when a file exists but cannot be read.
 *
 * This exception is raised when Globby attempts to read a file that exists but
 * is inaccessible due to insufficient permissions, I/O errors, or other system
 * constraints. This differs from FileNotFoundException which indicates the file
 * does not exist at all.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class FileUnreadableException extends RuntimeException implements GlobbyException
{
    /**
     * Create an exception instance for an unreadable file.
     *
     * Factory method that creates a new exception with a standardized error
     * message identifying the file path that could not be read.
     *
     * @param  string $path Absolute or relative path to the unreadable file
     * @return self   New exception instance with formatted error message
     */
    public static function forPath(string $path): self
    {
        return new self('Cannot read file: '.$path);
    }
}
