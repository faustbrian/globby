<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Contracts;

/**
 * Defines the contract for file system operations used by Globby.
 *
 * This interface abstracts file system operations to enable custom implementations
 * for testing environments, virtual file systems, or alternative storage backends.
 * The default implementation uses PHP's native file system functions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface FileSystemAdapter
{
    /**
     * Check if a path exists in the file system.
     *
     * Determines whether a file, directory, or symbolic link exists at the
     * specified path. Does not verify if the path is accessible or readable.
     *
     * @param  string $path Absolute or relative path to check for existence
     * @return bool   True if the path exists, false otherwise
     */
    public function exists(string $path): bool;

    /**
     * Check if a path is a directory.
     *
     * Verifies that the specified path exists and is a directory. Returns false
     * for files, symbolic links to files, broken links, or non-existent paths.
     *
     * @param  string $path Absolute or relative path to verify as directory
     * @return bool   True if the path exists and is a directory, false otherwise
     */
    public function isDirectory(string $path): bool;

    /**
     * Check if a path is a regular file.
     *
     * Verifies that the specified path exists and is a regular file. Returns false
     * for directories, symbolic links to directories, broken links, or non-existent paths.
     *
     * @param  string $path Absolute or relative path to verify as file
     * @return bool   True if the path exists and is a regular file, false otherwise
     */
    public function isFile(string $path): bool;

    /**
     * Read the complete contents of a file.
     *
     * Reads and returns the entire file contents as a string. This method should
     * throw exceptions for non-existent files, unreadable files, or I/O errors.
     *
     * @param  string $path Absolute or relative path to the file to read
     * @return string The complete file contents as a string
     */
    public function readFile(string $path): string;

    /**
     * Perform glob pattern matching to find pathnames.
     *
     * Searches for all pathnames matching the specified pattern according to the
     * rules used by the shell. Supports standard glob wildcards including * (any
     * characters), ? (single character), and [...] (character ranges).
     *
     * @param  string             $pattern Glob pattern to match (e.g., "*.php", "src/\*\*\/*.txt")
     * @param  int                $flags   Optional GLOB_* flags to modify behavior (GLOB_MARK, GLOB_NOSORT, etc.)
     * @return array<int, string> Array of absolute paths matching the pattern, empty array if no matches
     */
    public function glob(string $pattern, int $flags = 0): array;

    /**
     * Resolve the canonical absolute pathname.
     *
     * Expands all symbolic links, resolves relative path references (/./ and /../),
     * and removes extra slashes to return the canonical absolute pathname. Returns
     * false if the path does not exist or cannot be resolved.
     *
     * @param  string       $path Absolute or relative path to resolve to canonical form
     * @return false|string The resolved canonical absolute path, or false on failure
     */
    public function realpath(string $path): string|false;

    /**
     * Get the current working directory.
     *
     * Returns the absolute path of the current working directory from which the
     * process is executing. This is typically used to resolve relative paths.
     *
     * @return string The absolute path of the current working directory
     */
    public function getcwd(): string;
}
