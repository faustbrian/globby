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
 * @author Brian Faust <brian@cline.sh>
 */
final class PathNotDirectoryException extends RuntimeException implements GlobbyException
{
    /**
     * Create exception for path that is not a directory.
     *
     * @param  string $path The path that is not a directory
     * @return self   Exception instance
     */
    public static function forPath(string $path): self
    {
        return new self('Path is not a directory: '.$path);
    }
}
