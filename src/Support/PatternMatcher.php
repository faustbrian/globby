<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Support;

use Cline\Globby\Contracts\FileSystemAdapter;
use Cline\Globby\GlobbyOptions;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

use const DIRECTORY_SEPARATOR;
use const FNM_PATHNAME;
use const FNM_PERIOD;
use const GLOB_BRACE;
use const GLOB_MARK;

use function array_any;
use function array_merge;
use function basename;
use function dirname;
use function explode;
use function fnmatch;
use function mb_ltrim;
use function mb_rtrim;
use function mb_strlen;
use function mb_substr;
use function preg_match;
use function preg_quote;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function throw_unless;

/**
 * Handles glob pattern matching operations.
 *
 * Core pattern matching engine that supports standard glob patterns including:
 * - Single wildcard (*) matching any characters except path separators
 * - Globstar (**) for recursive directory matching across path separators
 * - Single character wildcard (?) matching exactly one character except /
 * - Character classes ([abc], [a-z]) and negation ([!abc])
 * - Brace expansion ({js,ts}) for multiple alternatives
 *
 * Handles both simple patterns (delegated to native glob) and complex recursive
 * patterns requiring directory iteration. Provides utility methods for pattern
 * analysis, escaping, and path-specific matching.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class PatternMatcher
{
    /**
     * Characters that make a pattern "dynamic" (not a literal path).
     *
     * Presence of any of these characters indicates the pattern requires
     * glob matching rather than direct file system lookup.
     */
    private const array GLOB_CHARS = ['*', '?', '[', ']', '{', '}'];

    /**
     * Create a new PatternMatcher instance.
     *
     * @param FileSystemAdapter $fs File system adapter for file operations and directory traversal
     */
    public function __construct(
        private FileSystemAdapter $fs,
    ) {}

    /**
     * Find all paths matching the given pattern.
     *
     * Main entry point for pattern matching. Determines the appropriate matching
     * strategy based on pattern characteristics (absolute, recursive, or simple)
     * and delegates to specialized methods. Returns absolute paths to all matches.
     *
     * @param  string        $pattern The glob pattern to match (absolute or relative)
     * @param  string        $cwd     Current working directory for resolving relative patterns
     * @param  GlobbyOptions $options Configuration options for filtering and traversal
     * @return array<string> Array of matching absolute file system paths
     */
    public function match(string $pattern, string $cwd, GlobbyOptions $options): array
    {
        // Handle absolute patterns
        if (str_starts_with($pattern, DIRECTORY_SEPARATOR)) {
            return $this->matchAbsolutePattern($pattern, $options);
        }

        // Handle ** recursive patterns
        if (str_contains($pattern, '**')) {
            return $this->matchRecursivePattern($pattern, $cwd, $options);
        }

        // Use native glob for simple patterns
        return $this->matchSimplePattern($pattern, $cwd, $options);
    }

    /**
     * Check if a path matches a specific pattern.
     *
     * Tests a single path against a glob pattern. Normalizes path separators,
     * converts to relative path if needed, and handles both standard patterns
     * and recursive globstar (**) patterns.
     *
     * @param  string $path    The file path to test (absolute or relative)
     * @param  string $pattern The glob pattern to match against
     * @param  string $cwd     Current working directory for path relativization
     * @return bool   True if the path matches the pattern
     */
    public function matchesPattern(string $path, string $pattern, string $cwd): bool
    {
        // Normalize path separators
        $path = str_replace('\\', '/', $path);
        $pattern = str_replace('\\', '/', $pattern);

        // Make path relative to cwd if needed
        $cwdNormalized = str_replace('\\', '/', $cwd);
        $prefix = $cwdNormalized.'/';

        if (str_starts_with($path, $prefix)) {
            $path = mb_substr($path, mb_strlen($prefix));
        }

        // Handle ** patterns
        if (str_contains($pattern, '**')) {
            return $this->matchGlobstar($path, $pattern);
        }

        // Use fnmatch for standard patterns
        return fnmatch($pattern, $path, FNM_PATHNAME | FNM_PERIOD);
    }

    /**
     * Check if a pattern contains dynamic glob characters.
     *
     * Determines whether a pattern string contains any special glob characters
     * that require pattern matching, or if it's a literal file path. Used to
     * optimize file lookups by skipping glob matching for literal paths.
     *
     * @param  string $pattern The pattern string to analyze
     * @return bool   True if the pattern contains glob metacharacters
     */
    public function isDynamic(string $pattern): bool
    {
        return array_any(self::GLOB_CHARS, fn ($char): bool => str_contains($pattern, (string) $char));
    }

    /**
     * Escape special glob characters in a path.
     *
     * Converts a file path into a literal glob pattern by escaping all
     * glob metacharacters. Useful when you need to match a path that
     * might contain characters like brackets or asterisks as literal characters.
     *
     * @param  string $path The file path to escape
     * @return string The escaped pattern safe for literal matching
     */
    public function escapePattern(string $path): string
    {
        // First convert Windows backslashes to forward slashes
        $normalized = str_replace('\\', '/', $path);

        // Then escape characters that have special meaning in glob patterns
        return str_replace(
            ['[', ']', '(', ')', '{', '}', '?', '*'],
            ['\\[', '\\]', '\\(', '\\)', '\\{', '\\}', '\\?', '\\*'],
            $normalized,
        );
    }

    /**
     * Match an absolute pattern.
     *
     * @param  string        $pattern The absolute pattern
     * @param  GlobbyOptions $options Options configuration
     * @return array<string> Matching paths
     */
    private function matchAbsolutePattern(string $pattern, GlobbyOptions $options): array
    {
        $flags = GLOB_BRACE;

        if (!$options->getOnlyFiles()) {
            $flags |= GLOB_MARK;
        }

        return $this->fs->glob($pattern, $flags);
    }

    /**
     * Match a simple (non-recursive) pattern.
     *
     * @param  string        $pattern The pattern
     * @param  string        $cwd     Current working directory
     * @param  GlobbyOptions $options Options configuration
     * @return array<string> Matching paths
     */
    private function matchSimplePattern(string $pattern, string $cwd, GlobbyOptions $options): array
    {
        $fullPattern = $cwd.DIRECTORY_SEPARATOR.$pattern;
        $flags = GLOB_BRACE;

        $results = $this->fs->glob($fullPattern, $flags);

        // Handle dotfiles if dot option is enabled
        if ($options->getDot()) {
            $dotResults = $this->matchDotfiles($fullPattern);
            $results = array_merge($results, $dotResults);
        }

        return $results;
    }

    /**
     * Match a recursive (**) pattern.
     *
     * @param  string        $pattern The pattern with **
     * @param  string        $cwd     Current working directory
     * @param  GlobbyOptions $options Options configuration
     * @return array<string> Matching paths
     */
    private function matchRecursivePattern(string $pattern, string $cwd, GlobbyOptions $options): array
    {
        $results = [];

        // Split pattern on **
        $parts = explode('**', $pattern, 2);
        $prefix = mb_rtrim($parts[0], DIRECTORY_SEPARATOR);
        $suffix = mb_ltrim($parts[1] ?? '', DIRECTORY_SEPARATOR);

        // Determine the base directory to start from
        $baseDir = $prefix !== '' ? $cwd.DIRECTORY_SEPARATOR.$prefix : $cwd;

        if (!$this->fs->isDirectory($baseDir)) {
            return [];
        }

        // Iterate through all files recursively
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $baseDir,
                    RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS,
                ),
                RecursiveIteratorIterator::SELF_FIRST,
            );

            $maxDepth = $options->getDeep();

            if ($maxDepth !== null) {
                $iterator->setMaxDepth($maxDepth);
            }

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                $path = $file->getPathname();

                // Skip dotfiles unless dot option is enabled
                $basename = basename($path);

                if (!$options->getDot() && str_starts_with($basename, '.')) {
                    continue;
                }

                // Check if the path matches the suffix pattern
                if ($suffix === '' || $suffix === '*' || $this->matchSuffix($path, $suffix, $baseDir)) {
                    $results[] = $path;
                }
            }
        } catch (Throwable $throwable) {
            throw_unless($options->getSuppressErrors(), $throwable);
        }

        /** @var array<string> $results */
        return $results;
    }

    /**
     * Match dotfiles for a pattern.
     *
     * @param  string        $pattern The pattern
     * @return array<string> Matching dotfiles
     */
    private function matchDotfiles(string $pattern): array
    {
        // Insert .* pattern for dotfiles
        $dir = dirname($pattern);
        $file = basename($pattern);
        $dotPattern = $dir.DIRECTORY_SEPARATOR.'.'.$file;

        return $this->fs->glob($dotPattern, GLOB_BRACE);
    }

    /**
     * Match a path against a globstar pattern.
     *
     * @param  string $path    The path to check
     * @param  string $pattern The pattern with **
     * @return bool   True if matches
     */
    private function matchGlobstar(string $path, string $pattern): bool
    {
        // Convert ** to regex
        $regex = $this->globstarToRegex($pattern);

        return preg_match($regex, $path) === 1;
    }

    /**
     * Convert a globstar pattern to regex.
     *
     * @param  string $pattern The glob pattern
     * @return string The regex pattern
     */
    private function globstarToRegex(string $pattern): string
    {
        // Escape regex special chars except glob chars
        $regex = preg_quote($pattern, '#');

        // Convert escaped glob patterns back
        $regex = str_replace('\*\*', '.*', $regex);
        $regex = str_replace('\*', '[^/]*', $regex);
        $regex = str_replace('\?', '[^/]', $regex);

        return '#^'.$regex.'$#';
    }

    /**
     * Check if a path matches a suffix pattern.
     *
     * @param  string $path    The full path
     * @param  string $suffix  The suffix pattern (after **)
     * @param  string $baseDir The base directory
     * @return bool   True if matches
     */
    private function matchSuffix(string $path, string $suffix, string $baseDir): bool
    {
        // Get relative path from base
        $relativePath = str_replace('\\', '/', $path);
        $baseNormalized = str_replace('\\', '/', $baseDir);

        if (str_starts_with($relativePath, $baseNormalized.'/')) {
            $relativePath = mb_substr($relativePath, mb_strlen($baseNormalized) + 1);
        }

        // Convert suffix to fnmatch pattern
        $suffixPattern = str_replace('\\', '/', $suffix);

        // Check if any part of the path matches the suffix
        return fnmatch($suffixPattern, basename($path), FNM_PATHNAME | FNM_PERIOD)
            || fnmatch('*/'.$suffixPattern, $relativePath, FNM_PATHNAME | FNM_PERIOD);
    }
}
