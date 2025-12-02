<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby;

use Cline\Globby\Contracts\FileSystemAdapter;
use Cline\Globby\Exceptions\BrokenSymbolicLinkException;
use Cline\Globby\Exceptions\DirectoryNotFoundException;
use Cline\Globby\Support\GitignoreParser;
use Cline\Globby\Support\NativeFileSystem;
use Cline\Globby\Support\PatternMatcher;
use Generator;
use Illuminate\Support\Str;
use SplFileInfo;

use const DIRECTORY_SEPARATOR;

use function array_all;
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function file_exists;
use function is_array;
use function is_link;
use function is_string;
use function mb_rtrim;
use function mb_strlen;
use function mb_substr;
use function readlink;
use function sort;
use function str_starts_with;

/**
 * Core manager for Globby glob operations.
 *
 * Provides user-friendly glob matching with support for multiple patterns,
 * negation patterns, directory expansion, and gitignore integration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class GlobbyManager
{
    /**
     * Pattern matcher for glob pattern operations.
     */
    private PatternMatcher $matcher;

    /**
     * Gitignore parser for handling .gitignore files.
     */
    private GitignoreParser $gitignoreParser;

    /**
     * Create a new GlobbyManager instance.
     *
     * @param FileSystemAdapter $fs Custom file system adapter (optional)
     */
    public function __construct(
        private FileSystemAdapter $fs = new NativeFileSystem(),
    ) {
        $this->matcher = new PatternMatcher($this->fs);
        $this->gitignoreParser = new GitignoreParser($this->fs);
    }

    /**
     * Find files matching the given glob patterns.
     *
     * @param  array<string>|string      $patterns Single pattern or array of patterns
     * @param  null|array<string, mixed> $options  Options array or null for defaults
     * @return array<GlobEntry|string>   Array of matching file paths or GlobEntry objects
     */
    public function glob(array|string $patterns, ?array $options = null): array
    {
        $opts = $options !== null ? GlobbyOptions::fromArray($options) : GlobbyOptions::create();

        return $this->globWithOptions($patterns, $opts);
    }

    /**
     * Find files matching the given glob patterns with options object.
     *
     * @param array<string>|string $patterns Single pattern or array of patterns
     * @param GlobbyOptions        $options  Options configuration object
     *
     * @throws BrokenSymbolicLinkException When throwErrorOnBrokenSymbolicLink is enabled
     *
     * @return array<GlobEntry|string> Array of matching file paths or GlobEntry objects
     */
    public function globWithOptions(array|string $patterns, GlobbyOptions $options): array
    {
        $patterns = $this->normalizePatterns($patterns);
        $cwd = $this->resolveCwd($options);

        // Apply custom file system if provided
        if ($options->getFs() instanceof FileSystemAdapter) {
            $this->fs = $options->getFs();
            $this->matcher = new PatternMatcher($this->fs);
            $this->gitignoreParser = new GitignoreParser($this->fs);
        }

        // Separate positive and negative patterns
        [$positive, $negative] = $this->separatePatterns($patterns);

        // If only negative patterns, prepend **/*
        if ($positive === [] && $negative !== []) {
            $positive = ['**/*'];
        }

        // Apply directory expansion to positive patterns
        $positive = $this->expandDirectoryPatterns($positive, $cwd, $options);

        // Collect all matching paths
        $matches = [];

        foreach ($positive as $pattern) {
            $patternMatches = $this->matcher->match($pattern, $cwd, $options);
            $matches = array_merge($matches, $patternMatches);
        }

        // Apply negative patterns (exclusions)
        $matches = $this->applyNegativePatterns($matches, $negative, $cwd);

        // Apply gitignore filtering
        if ($options->getGitignore()) {
            $matches = $this->applyGitignore($matches, $cwd, $options);
        }

        // Apply ignore files filtering
        $ignoreFiles = $options->getIgnoreFiles();

        if ($ignoreFiles !== null) {
            $matches = $this->applyIgnoreFiles($matches, $ignoreFiles, $cwd);
        }

        // Apply manual ignore patterns
        if ($options->getIgnore() !== []) {
            $matches = $this->applyIgnorePatterns($matches, $options->getIgnore(), $cwd);
        }

        // Filter by file type
        $matches = $this->filterByType($matches, $options);

        // Check for broken symbolic links
        if ($options->getThrowErrorOnBrokenSymbolicLink()) {
            $this->checkBrokenSymlinks($matches);
        }

        // Apply markDirectories option
        if ($options->getMarkDirectories()) {
            $matches = $this->markDirectories($matches);
        }

        // Make paths absolute if requested
        $matches = $options->getAbsolute() ? $this->makeAbsolute($matches, $cwd) : $this->makeRelative($matches, $cwd);

        // Ensure unique results
        if ($options->getUnique()) {
            $matches = array_unique($matches);
        }

        // Sort results
        sort($matches);

        // Convert to GlobEntry objects if objectMode is enabled
        if ($options->getObjectMode()) {
            return $this->convertToEntries($matches, $options->getStats(), $cwd);
        }

        /** @var list<string> $matches */
        return $matches;
    }

    /**
     * Stream files matching the given glob patterns.
     *
     * @param  array<string>|string        $patterns Single pattern or array of patterns
     * @param  null|array<string, mixed>   $options  Options array or null for defaults
     * @return Generator<GlobEntry|string> Generator yielding matching file paths or GlobEntry objects
     */
    public function stream(array|string $patterns, ?array $options = null): Generator
    {
        $matches = $this->glob($patterns, $options);

        foreach ($matches as $match) {
            yield $match;
        }
    }

    /**
     * Check if a pattern contains dynamic (glob) characters.
     *
     * @param  string $pattern The pattern to check
     * @return bool   True if the pattern contains glob characters
     */
    public function isDynamicPattern(string $pattern): bool
    {
        return $this->matcher->isDynamic($pattern);
    }

    /**
     * Convert a path to a glob pattern by escaping special characters.
     *
     * @param  string $path The path to convert
     * @return string The escaped pattern
     */
    public function convertPathToPattern(string $path): string
    {
        return $this->matcher->escapePattern($path);
    }

    /**
     * Check if a path is ignored by gitignore rules.
     *
     * @param  string                    $path    The path to check
     * @param  null|array<string, mixed> $options Options array
     * @return bool                      True if the path is ignored
     */
    public function isGitIgnored(string $path, ?array $options = null): bool
    {
        $opts = $options !== null ? GlobbyOptions::fromArray($options) : GlobbyOptions::create();
        $cwd = $this->resolveCwd($opts);

        return $this->gitignoreParser->isIgnored($path, $cwd, $opts);
    }

    /**
     * Check if a path is ignored by specified ignore files.
     *
     * @param  string                    $path        The path to check
     * @param  array<string>|string      $ignoreFiles Patterns to find ignore files
     * @param  null|array<string, mixed> $options     Options array
     * @return bool                      True if the path is ignored
     */
    public function isIgnoredByIgnoreFiles(string $path, array|string $ignoreFiles, ?array $options = null): bool
    {
        $opts = $options !== null ? GlobbyOptions::fromArray($options) : GlobbyOptions::create();
        $cwd = $this->resolveCwd($opts);
        $ignorePatterns = is_string($ignoreFiles) ? [$ignoreFiles] : $ignoreFiles;

        return $this->gitignoreParser->isIgnoredByFiles($path, $ignorePatterns, $cwd);
    }

    /**
     * Generate glob tasks from patterns for use with other libraries.
     *
     * @param  array<string>|string                                                 $patterns Single pattern or array of patterns
     * @param  null|array<string, mixed>                                            $options  Options array
     * @return array<array{patterns: array<string>, options: array<string, mixed>}> Array of glob tasks
     */
    public function generateGlobTasks(array|string $patterns, ?array $options = null): array
    {
        $patterns = $this->normalizePatterns($patterns);
        $opts = $options !== null ? GlobbyOptions::fromArray($options) : GlobbyOptions::create();
        $cwd = $this->resolveCwd($opts);

        [$positive, $negative] = $this->separatePatterns($patterns);

        if ($positive === [] && $negative !== []) {
            $positive = ['**/*'];
        }

        $positive = $this->expandDirectoryPatterns($positive, $cwd, $opts);

        return [
            [
                'patterns' => $positive,
                'options' => array_merge($opts->toArray(), ['negative' => $negative]),
            ],
        ];
    }

    /**
     * Normalize patterns to array format.
     *
     * @param  array<string>|string $patterns Input patterns
     * @return array<string>        Normalized array of patterns
     */
    private function normalizePatterns(array|string $patterns): array
    {
        if (is_string($patterns)) {
            return [$patterns];
        }

        return $patterns;
    }

    /**
     * Resolve the current working directory from options.
     *
     * @param GlobbyOptions $options Options with potential cwd setting
     *
     * @throws DirectoryNotFoundException If cwd doesn't exist
     *
     * @return string Resolved absolute cwd path
     */
    private function resolveCwd(GlobbyOptions $options): string
    {
        $cwd = $options->getCwd() ?? $this->fs->getcwd();
        $realCwd = $this->fs->realpath($cwd);

        if ($realCwd === false || !$this->fs->isDirectory($realCwd)) {
            throw DirectoryNotFoundException::forPath($cwd);
        }

        return $realCwd;
    }

    /**
     * Separate patterns into positive (include) and negative (exclude) groups.
     *
     * @param  array<string>                             $patterns All patterns
     * @return array{0: array<string>, 1: array<string>} Tuple of [positive, negative]
     */
    private function separatePatterns(array $patterns): array
    {
        $positive = [];
        $negative = [];

        foreach ($patterns as $pattern) {
            if (str_starts_with($pattern, '!')) {
                $negative[] = mb_substr($pattern, 1);
            } else {
                $positive[] = $pattern;
            }
        }

        return [$positive, $negative];
    }

    /**
     * Expand directory patterns based on options configuration.
     *
     * @param  array<string> $patterns Patterns to expand
     * @param  string        $cwd      Current working directory
     * @param  GlobbyOptions $options  Options configuration
     * @return array<string> Expanded patterns
     */
    private function expandDirectoryPatterns(array $patterns, string $cwd, GlobbyOptions $options): array
    {
        $expand = $options->getExpandDirectories();

        if ($expand === false) {
            return $patterns;
        }

        $expanded = [];

        foreach ($patterns as $pattern) {
            // Check if pattern matches an existing directory
            $fullPath = $cwd.DIRECTORY_SEPARATOR.$pattern;
            $resolvedPath = $this->fs->realpath($fullPath);

            if ($resolvedPath !== false && $this->fs->isDirectory($resolvedPath)) {
                // Expand directory pattern
                if ($expand === true) {
                    $expanded[] = mb_rtrim($pattern, DIRECTORY_SEPARATOR).'/**/*';
                } elseif (is_array($expand)) {
                    // Custom expansion based on files/extensions
                    $expandedPatterns = $this->buildExpandedPatterns($pattern, $expand);
                    $expanded = array_merge($expanded, $expandedPatterns);
                }
            } else {
                $expanded[] = $pattern;
            }
        }

        return $expanded;
    }

    /**
     * Build expanded patterns from directory expansion configuration.
     *
     * @param  string                                                   $basePattern Base directory pattern
     * @param  array{files?: array<string>, extensions?: array<string>} $config      Expansion configuration
     * @return array<string>                                            Expanded patterns
     */
    private function buildExpandedPatterns(string $basePattern, array $config): array
    {
        $patterns = [];
        $base = mb_rtrim($basePattern, DIRECTORY_SEPARATOR);

        if (array_key_exists('files', $config)) {
            foreach ($config['files'] as $file) {
                $patterns[] = $base.'/**/'.$file;
            }
        }

        if (array_key_exists('extensions', $config)) {
            foreach ($config['extensions'] as $ext) {
                $patterns[] = $base.'/**/*.'.$ext;
            }
        }

        // If no specific config, expand all
        if ($patterns === []) {
            $patterns[] = $base.'/**/*';
        }

        return $patterns;
    }

    /**
     * Apply negative (exclusion) patterns to matches.
     *
     * @param  array<string> $matches  Current matches
     * @param  array<string> $negative Negative patterns
     * @param  string        $cwd      Current working directory
     * @return array<string> Filtered matches
     */
    private function applyNegativePatterns(array $matches, array $negative, string $cwd): array
    {
        if ($negative === []) {
            return $matches;
        }

        return array_filter($matches, fn (string $path): bool => array_all($negative, fn (string $pattern): bool => !$this->matcher->matchesPattern($path, $pattern, $cwd)));
    }

    /**
     * Apply gitignore filtering to matches.
     *
     * @param  array<string> $matches Current matches
     * @param  string        $cwd     Current working directory
     * @param  GlobbyOptions $options Options configuration
     * @return array<string> Filtered matches
     */
    private function applyGitignore(array $matches, string $cwd, GlobbyOptions $options): array
    {
        return array_filter($matches, fn (string $path): bool => !$this->gitignoreParser->isIgnored($path, $cwd, $options));
    }

    /**
     * Apply ignore files filtering to matches.
     *
     * @param  array<string>        $matches     Current matches
     * @param  array<string>|string $ignoreFiles Patterns to find ignore files
     * @param  string               $cwd         Current working directory
     * @return array<string>        Filtered matches
     */
    private function applyIgnoreFiles(array $matches, array|string $ignoreFiles, string $cwd): array
    {
        $ignorePatterns = is_string($ignoreFiles) ? [$ignoreFiles] : $ignoreFiles;

        return array_filter(
            $matches,
            fn (string $path): bool => !$this->gitignoreParser->isIgnoredByFiles($path, $ignorePatterns, $cwd),
        );
    }

    /**
     * Apply manual ignore patterns to matches.
     *
     * @param  array<string> $matches  Current matches
     * @param  array<string> $patterns Patterns to exclude
     * @param  string        $cwd      Current working directory
     * @return array<string> Filtered matches
     */
    private function applyIgnorePatterns(array $matches, array $patterns, string $cwd): array
    {
        return array_filter($matches, fn (string $path): bool => array_all($patterns, fn (string $pattern): bool => !$this->matcher->matchesPattern($path, $pattern, $cwd)));
    }

    /**
     * Filter matches by file type (files only, directories only, or both).
     *
     * @param  array<string> $matches Current matches
     * @param  GlobbyOptions $options Options with type configuration
     * @return array<string> Filtered matches
     */
    private function filterByType(array $matches, GlobbyOptions $options): array
    {
        if (!$options->getOnlyFiles() && !$options->getOnlyDirectories()) {
            return $matches;
        }

        return array_filter($matches, function (string $path) use ($options): bool {
            if ($options->getOnlyFiles()) {
                return $this->fs->isFile($path);
            }

            if ($options->getOnlyDirectories()) {
                return $this->fs->isDirectory($path);
            }

            return true;
        });
    }

    /**
     * Convert paths to absolute format.
     *
     * @param  array<string> $matches Paths to convert
     * @param  string        $cwd     Current working directory
     * @return array<string> Absolute paths
     */
    private function makeAbsolute(array $matches, string $cwd): array
    {
        return array_map(function (string $path) use ($cwd): string {
            if (Str::startsWith($path, DIRECTORY_SEPARATOR)) {
                return $path;
            }

            return $cwd.DIRECTORY_SEPARATOR.$path;
        }, $matches);
    }

    /**
     * Convert paths to relative format.
     *
     * @param  array<string> $matches Paths to convert
     * @param  string        $cwd     Current working directory
     * @return array<string> Relative paths
     */
    private function makeRelative(array $matches, string $cwd): array
    {
        $prefix = $cwd.DIRECTORY_SEPARATOR;

        return array_map(function (string $path) use ($prefix): string {
            if (Str::startsWith($path, $prefix)) {
                return mb_substr($path, mb_strlen($prefix));
            }

            return $path;
        }, $matches);
    }

    /**
     * Check for broken symbolic links and throw if found.
     *
     * @param array<string> $matches Paths to check
     *
     * @throws BrokenSymbolicLinkException If a broken symbolic link is found
     */
    private function checkBrokenSymlinks(array $matches): void
    {
        foreach ($matches as $path) {
            if (is_link($path)) {
                $target = readlink($path);

                if ($target !== false && !file_exists($path)) {
                    throw BrokenSymbolicLinkException::forPath($path);
                }
            }
        }
    }

    /**
     * Mark directories with trailing slashes.
     *
     * @param  array<string> $matches Paths to process
     * @return array<string> Paths with directories marked
     */
    private function markDirectories(array $matches): array
    {
        return array_map(function (string $path): string {
            if ($this->fs->isDirectory($path) && !Str::endsWith($path, DIRECTORY_SEPARATOR)) {
                return $path.DIRECTORY_SEPARATOR;
            }

            return $path;
        }, $matches);
    }

    /**
     * Convert string paths to GlobEntry objects.
     *
     * @param  array<string>    $matches      Paths to convert
     * @param  bool             $includeStats Whether to include file statistics
     * @param  null|string      $cwd          Current working directory for resolving relative paths
     * @return array<GlobEntry> Array of GlobEntry objects
     */
    private function convertToEntries(array $matches, bool $includeStats, ?string $cwd = null): array
    {
        return array_map(
            function (string $path) use ($includeStats, $cwd): GlobEntry {
                // Resolve path for stats if needed
                $absolutePath = $path;

                if ($cwd !== null && !str_starts_with($path, DIRECTORY_SEPARATOR)) {
                    $absolutePath = $cwd.DIRECTORY_SEPARATOR.$path;
                }

                // Create dirent from resolved path
                $dirent = new SplFileInfo($absolutePath);

                // Create stats from resolved path if needed
                $stats = null;

                if ($includeStats) {
                    $stats = GlobEntryStats::fromPath($absolutePath);
                }

                // But keep the original path in the entry
                return new GlobEntry(
                    path: $path,
                    name: $dirent->getBasename(),
                    dirent: $dirent,
                    stats: $stats,
                );
            },
            array_values($matches),
        );
    }
}
