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
 * Exception thrown when a symbolic link points to a non-existent target.
 *
 * This exception is raised when Globby encounters a symbolic link that cannot
 * be resolved because its target path does not exist. This can occur during
 * directory traversal, pattern matching, or file operations.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class BrokenSymbolicLinkException extends RuntimeException implements GlobbyException
{
    /**
     * Create an exception instance for a broken symbolic link.
     *
     * Factory method that creates a new exception with a standardized error
     * message identifying the path of the broken symbolic link.
     *
     * @param  string $path Absolute or relative path to the broken symbolic link
     * @return self   New exception instance with formatted error message
     */
    public static function forPath(string $path): self
    {
        return new self('Broken symbolic link: '.$path);
    }
}
