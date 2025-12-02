<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Contracts;

/**
 * Interface for file system operations used by Globby.
 *
 * Allows custom file system implementations for testing or virtual file systems.
 * The default implementation uses PHP's native file system functions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface FileSystemAdapter
{
    /**
     * Check if a path exists.
     *
     * @param  string $path The path to check
     * @return bool   True if the path exists, false otherwise
     */
    public function exists(string $path): bool;

    /**
     * Check if a path is a directory.
     *
     * @param  string $path The path to check
     * @return bool   True if the path is a directory, false otherwise
     */
    public function isDirectory(string $path): bool;

    /**
     * Check if a path is a file.
     *
     * @param  string $path The path to check
     * @return bool   True if the path is a file, false otherwise
     */
    public function isFile(string $path): bool;

    /**
     * Read the contents of a file.
     *
     * @param  string $path The path to read
     * @return string The file contents
     */
    public function readFile(string $path): string;

    /**
     * Perform glob pattern matching.
     *
     * @param  string        $pattern The glob pattern
     * @param  int           $flags   Optional GLOB_* flags
     * @return array<string> Array of matching paths
     */
    public function glob(string $pattern, int $flags = 0): array;

    /**
     * Get the real path of a file.
     *
     * @param  string       $path The path to resolve
     * @return false|string The real path, or false on failure
     */
    public function realpath(string $path): false|string;

    /**
     * Get the current working directory.
     *
     * @return string The current working directory
     */
    public function getcwd(): string;
}
