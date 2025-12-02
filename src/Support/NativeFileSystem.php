<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Support;

use Cline\Globby\Contracts\FileSystemAdapter;

use function file_exists;
use function file_get_contents;
use function getcwd;
use function glob;
use function is_dir;
use function is_file;
use function realpath;
use function restore_error_handler;
use function set_error_handler;

/**
 * Native PHP file system adapter implementation.
 *
 * Uses PHP's built-in file system functions for all operations.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class NativeFileSystem implements FileSystemAdapter
{
    /**
     * {@inheritDoc}
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * {@inheritDoc}
     */
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * {@inheritDoc}
     */
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * {@inheritDoc}
     */
    public function readFile(string $path): string
    {
        set_error_handler(static fn (): bool => true);

        try {
            $contents = file_get_contents($path);
        } finally {
            restore_error_handler();
        }

        return $contents !== false ? $contents : '';
    }

    /**
     * {@inheritDoc}
     */
    public function glob(string $pattern, int $flags = 0): array
    {
        $result = glob($pattern, $flags);

        return $result !== false ? $result : [];
    }

    /**
     * {@inheritDoc}
     */
    public function realpath(string $path): false|string
    {
        return realpath($path);
    }

    /**
     * {@inheritDoc}
     */
    public function getcwd(): string
    {
        $cwd = getcwd();

        return $cwd !== false ? $cwd : '';
    }
}
