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
 * Exception thrown when a file does not exist at the specified path.
 *
 * This exception is raised when Globby attempts to access a file that does not
 * exist in the file system. This can occur during file read operations, when
 * verifying file existence, or when resolving patterns to specific files.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class FileNotFoundException extends RuntimeException implements GlobbyException
{
    /**
     * Create an exception instance for a non-existent file.
     *
     * Factory method that creates a new exception with a standardized error
     * message identifying the file path that was not found.
     *
     * @param  string $path Absolute or relative path to the non-existent file
     * @return self   New exception instance with formatted error message
     */
    public static function forPath(string $path): self
    {
        return new self('File not found: '.$path);
    }
}
