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
 * Exception thrown when a directory does not exist at the specified path.
 *
 * This exception is raised when Globby attempts to access or traverse a directory
 * that does not exist in the file system. This can occur during pattern matching,
 * directory scanning, or when validating base paths for glob operations.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class DirectoryNotFoundException extends RuntimeException implements GlobbyException
{
    /**
     * Create an exception instance for a non-existent directory.
     *
     * Factory method that creates a new exception with a standardized error
     * message identifying the directory path that was not found.
     *
     * @param  string $path Absolute or relative path to the non-existent directory
     * @return self   New exception instance with formatted error message
     */
    public static function forPath(string $path): self
    {
        return new self('Directory not found: '.$path);
    }
}
