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
 * Default implementation using PHP's built-in file system functions.
 * Provides a thin wrapper around native PHP functions with consistent
 * error handling and return types. All operations work on the real
 * file system without any virtualization or mocking.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class NativeFileSystem implements FileSystemAdapter
{
    /**
     * Check if a file or directory exists.
     *
     * @param  string $path The file or directory path to check
     * @return bool   True if the path exists (file, directory, or symlink)
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Check if a path is a directory.
     *
     * @param  string $path The path to check
     * @return bool   True if the path is a directory (not a file or symlink to file)
     */
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * Check if a path is a regular file.
     *
     * @param  string $path The path to check
     * @return bool   True if the path is a regular file (not a directory or special file)
     */
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * Read entire file contents into a string.
     *
     * Errors are suppressed and empty string is returned on failure.
     * No exception is thrown for missing files or permission errors.
     *
     * @param  string $path The absolute path to the file to read
     * @return string The complete file contents, or empty string on error
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
     * Find pathnames matching a pattern.
     *
     * Uses PHP's native glob() function. Returns empty array on error
     * rather than false. Supports standard glob flags like GLOB_BRACE.
     *
     * @param  string        $pattern The glob pattern to match
     * @param  int           $flags   Optional glob flags (GLOB_MARK, GLOB_BRACE, etc.)
     * @return array<string> Array of matching file paths, or empty array on error
     */
    public function glob(string $pattern, int $flags = 0): array
    {
        $result = glob($pattern, $flags);

        return $result !== false ? $result : [];
    }

    /**
     * Resolve canonical absolute pathname.
     *
     * Expands symlinks and resolves relative path references (. and ..).
     * Returns false if the path doesn't exist.
     *
     * @param  string       $path The path to resolve
     * @return false|string Canonical absolute path, or false if path doesn't exist
     */
    public function realpath(string $path): false|string
    {
        return realpath($path);
    }

    /**
     * Get current working directory.
     *
     * Returns the current PHP process working directory. Returns empty
     * string on error rather than false for consistent string return type.
     *
     * @return string The current working directory path, or empty string on error
     */
    public function getcwd(): string
    {
        $cwd = getcwd();

        return $cwd !== false ? $cwd : '';
    }
}
