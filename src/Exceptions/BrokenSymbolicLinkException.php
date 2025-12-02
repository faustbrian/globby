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
 * Exception thrown when a broken symbolic link is encountered.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class BrokenSymbolicLinkException extends RuntimeException implements GlobbyException
{
    /**
     * Create exception for a broken symbolic link.
     *
     * @param string $path Path to the broken symbolic link
     */
    public static function forPath(string $path): self
    {
        return new self('Broken symbolic link: '.$path);
    }
}
