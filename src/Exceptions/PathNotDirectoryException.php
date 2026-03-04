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
 * Exception thrown when a path exists but is not a directory.
 *
 * This exception is raised when Globby attempts to perform a directory operation
 * on a path that exists as a regular file, symbolic link to a file, or other
 * non-directory type. This distinguishes from DirectoryNotFoundException where
 * the path does not exist at all.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PathNotDirectoryException extends RuntimeException implements GlobbyException
{
    /**
     * Create an exception instance for a path that is not a directory.
     *
     * Factory method that creates a new exception with a standardized error
     * message identifying the path that exists but is not a directory.
     *
     * @param  string $path Absolute or relative path that exists but is not a directory
     * @return self   New exception instance with formatted error message
     */
    public static function forPath(string $path): self
    {
        return new self('Path is not a directory: '.$path);
    }
}
