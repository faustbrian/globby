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
use const PHP_INT_MAX;

use function array_key_exists;
use function array_merge;
use function basename;
use function dirname;
use function explode;
use function fnmatch;
use function mb_rtrim;
use function mb_strlen;
use function mb_substr;
use function mb_trim;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;

/**
 * Parser for .gitignore and similar ignore files.
 *
 * Handles parsing of gitignore patterns and checking if paths match
 * any of the ignore rules. Supports negation patterns and directory-specific rules.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class GitignoreParser
{
    /**
     * Cache of parsed ignore rules by directory.
     *
     * @var array<string, array<array{pattern: string, negated: bool, directory: bool, base: string}>>
     */
    private array $cache = [];

    /**
     * Create a new GitignoreParser instance.
     *
     * @param FileSystemAdapter $fs File system adapter
     */
    public function __construct(
        private readonly FileSystemAdapter $fs,
    ) {}

    /**
     * Check if a path is ignored by gitignore rules.
     *
     * @param  string        $path    The path to check
     * @param  string        $cwd     Current working directory
     * @param  GlobbyOptions $options Options configuration
     * @return bool          True if the path should be ignored
     */
    public function isIgnored(string $path, string $cwd, GlobbyOptions $options): bool
    {
        // Find all .gitignore files from cwd down and up to git root
        $rules = $this->collectGitignoreRules($cwd, $options);

        return $this->pathMatchesRules($path, $rules, $cwd);
    }

    /**
     * Check if a path is ignored by specified ignore files.
     *
     * @param  string        $path        The path to check
     * @param  array<string> $ignoreFiles Patterns to find ignore files
     * @param  string        $cwd         Current working directory
     * @return bool          True if the path should be ignored
     */
    public function isIgnoredByFiles(string $path, array $ignoreFiles, string $cwd): bool
    {
        $rules = $this->collectIgnoreFileRules($ignoreFiles, $cwd);

        return $this->pathMatchesRules($path, $rules, $cwd);
    }

    /**
     * Collect all gitignore rules starting from the current directory.
     *
     * @param  string                                                                      $cwd     Current working directory
     * @param  GlobbyOptions                                                               $options Options configuration
     * @return array<array{pattern: string, negated: bool, directory: bool, base: string}> Collected rules
     */
    private function collectGitignoreRules(string $cwd, GlobbyOptions $options): array
    {
        $rules = [];

        // Check for .gitignore in current directory
        $gitignorePath = $cwd.DIRECTORY_SEPARATOR.'.gitignore';

        if ($this->fs->exists($gitignorePath)) {
            $rules = array_merge($rules, $this->parseGitignoreFile($gitignorePath, $cwd));
        }

        // Look for git repository root and collect parent .gitignore files
        $gitRoot = $this->findGitRoot($cwd);

        if ($gitRoot !== null && $gitRoot !== $cwd) {
            $currentDir = dirname($cwd);

            while ($currentDir !== $gitRoot && mb_strlen($currentDir) >= mb_strlen($gitRoot)) {
                $parentGitignore = $currentDir.DIRECTORY_SEPARATOR.'.gitignore';

                if ($this->fs->exists($parentGitignore)) {
                    $parentRules = $this->parseGitignoreFile($parentGitignore, $currentDir);
                    $rules = array_merge($parentRules, $rules);
                }

                $newDir = dirname($currentDir);

                if ($newDir === $currentDir) {
                    break;
                }

                $currentDir = $newDir;
            }
        }

        // Also scan subdirectories for .gitignore files
        $depth = $options->getDeep() ?? PHP_INT_MAX;
        $subRules = $this->scanForGitignoreFiles($cwd, $depth);

        return array_merge($rules, $subRules);
    }

    /**
     * Find the git repository root (directory containing .git).
     *
     * @param  string      $startDir Starting directory
     * @return null|string Git root directory or null if not in a git repo
     */
    private function findGitRoot(string $startDir): ?string
    {
        $currentDir = $startDir;

        while (true) {
            $gitDir = $currentDir.DIRECTORY_SEPARATOR.'.git';

            if ($this->fs->exists($gitDir)) {
                return $currentDir;
            }

            $parentDir = dirname($currentDir);

            if ($parentDir === $currentDir) {
                break;
            }

            $currentDir = $parentDir;
        }

        return null;
    }

    /**
     * Scan for .gitignore files in subdirectories.
     *
     * @param  string                                                                      $baseDir  Base directory to scan
     * @param  int                                                                         $maxDepth Maximum depth to scan
     * @return array<array{pattern: string, negated: bool, directory: bool, base: string}> Collected rules
     */
    private function scanForGitignoreFiles(string $baseDir, int $maxDepth): array
    {
        $rules = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $baseDir,
                    RecursiveDirectoryIterator::SKIP_DOTS,
                ),
                RecursiveIteratorIterator::SELF_FIRST,
            );

            $iterator->setMaxDepth($maxDepth);

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }

                if ($file->getFilename() === '.gitignore') {
                    $gitignoreDir = dirname($file->getPathname());

                    if ($gitignoreDir !== $baseDir) {
                        $rules = array_merge($rules, $this->parseGitignoreFile($file->getPathname(), $gitignoreDir));
                    }
                }
            }
        } catch (Throwable) {
            // Silently ignore errors during scanning
        }

        return $rules;
    }

    /**
     * Collect rules from specified ignore files.
     *
     * @param  array<string>                                                               $ignoreFiles Patterns to find ignore files
     * @param  string                                                                      $cwd         Current working directory
     * @return array<array{pattern: string, negated: bool, directory: bool, base: string}> Collected rules
     */
    private function collectIgnoreFileRules(array $ignoreFiles, string $cwd): array
    {
        $rules = [];

        foreach ($ignoreFiles as $ignoreFile) {
            // Check if it's a direct path or a glob pattern
            if (str_contains($ignoreFile, '*')) {
                // It's a glob pattern - find matching files
                $matches = $this->fs->glob($cwd.DIRECTORY_SEPARATOR.$ignoreFile);

                foreach ($matches as $match) {
                    if ($this->fs->isFile($match)) {
                        $rules = array_merge($rules, $this->parseGitignoreFile($match, dirname($match)));
                    }
                }
            } else {
                // Direct path
                $filePath = $cwd.DIRECTORY_SEPARATOR.$ignoreFile;

                if ($this->fs->exists($filePath) && $this->fs->isFile($filePath)) {
                    $rules = array_merge($rules, $this->parseGitignoreFile($filePath, $cwd));
                }
            }
        }

        return $rules;
    }

    /**
     * Parse a gitignore file into rules.
     *
     * @param  string                                                                      $path    Path to the gitignore file
     * @param  string                                                                      $baseDir Base directory for the rules
     * @return array<array{pattern: string, negated: bool, directory: bool, base: string}> Parsed rules
     */
    private function parseGitignoreFile(string $path, string $baseDir): array
    {
        // Check cache
        if (array_key_exists($path, $this->cache)) {
            return $this->cache[$path];
        }

        $content = $this->fs->readFile($path);
        $lines = explode("\n", $content);
        $rules = [];

        foreach ($lines as $line) {
            $line = mb_trim($line);

            // Skip empty lines and comments
            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '#')) {
                continue;
            }

            $negated = false;
            $directory = false;

            // Check for negation
            if (str_starts_with($line, '!')) {
                $negated = true;
                $line = mb_substr($line, 1);
            }

            // Check for directory-only pattern
            if (str_ends_with($line, '/')) {
                $directory = true;
                $line = mb_rtrim($line, '/');
            }

            // Normalize the pattern
            $pattern = $this->normalizePattern($line);

            $rules[] = [
                'pattern' => $pattern,
                'negated' => $negated,
                'directory' => $directory,
                'base' => $baseDir,
            ];
        }

        $this->cache[$path] = $rules;

        return $rules;
    }

    /**
     * Normalize a gitignore pattern.
     *
     * @param  string $pattern The raw pattern
     * @return string Normalized pattern
     */
    private function normalizePattern(string $pattern): string
    {
        // Remove leading/trailing slashes for matching
        $pattern = mb_trim($pattern, '/');

        // Handle patterns that should match from root
        if (str_contains($pattern, '/')) {
            // Pattern with path separator - match from base
            return $pattern;
        }

        // Pattern without separator - can match at any level
        return '**/'.$pattern;
    }

    /**
     * Check if a path matches any of the rules.
     *
     * @param  string                                                                      $path  The path to check
     * @param  array<array{pattern: string, negated: bool, directory: bool, base: string}> $rules The rules to check against
     * @param  string                                                                      $cwd   Current working directory
     * @return bool                                                                        True if path should be ignored
     */
    private function pathMatchesRules(string $path, array $rules, string $cwd): bool
    {
        $isIgnored = false;

        // Normalize path
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedCwd = str_replace('\\', '/', $cwd);

        // Make path relative to cwd
        if (str_starts_with($normalizedPath, $normalizedCwd.'/')) {
            $relativePath = mb_substr($normalizedPath, mb_strlen($normalizedCwd) + 1);
        } else {
            $relativePath = $normalizedPath;
        }

        // Check each rule in order (later rules can override earlier ones)
        foreach ($rules as $rule) {
            // Make the path relative to the rule's base directory
            $ruleBase = str_replace('\\', '/', $rule['base']);
            $pathFromRuleBase = $relativePath;

            if (str_starts_with($normalizedCwd.'/'.$relativePath, $ruleBase.'/')) {
                $pathFromRuleBase = mb_substr($normalizedCwd.'/'.$relativePath, mb_strlen($ruleBase) + 1);
            }

            if ($this->patternMatches($pathFromRuleBase, $rule['pattern'], $rule['directory'], $path)) {
                $isIgnored = !$rule['negated'];
            }
        }

        return $isIgnored;
    }

    /**
     * Check if a pattern matches a path.
     *
     * @param  string $path          The path to check
     * @param  string $pattern       The pattern
     * @param  bool   $directoryOnly Whether the pattern only matches directories
     * @param  string $fullPath      The full path for directory checking
     * @return bool   True if pattern matches
     */
    private function patternMatches(string $path, string $pattern, bool $directoryOnly, string $fullPath): bool
    {
        // If pattern only matches directories, check if path is a directory
        if ($directoryOnly && !$this->fs->isDirectory($fullPath)) {
            return false;
        }

        // Convert gitignore pattern to fnmatch pattern
        $fnmatchPattern = $this->gitignoreToFnmatch($pattern);

        // Try matching the full path
        if (fnmatch($fnmatchPattern, $path, FNM_PATHNAME | FNM_PERIOD)) {
            return true;
        }

        // Also try matching just the basename for patterns like "*.log"
        $basename = basename($path);

        return !str_contains($pattern, '/') && fnmatch($fnmatchPattern, $basename, FNM_PATHNAME | FNM_PERIOD);
    }

    /**
     * Convert a gitignore pattern to fnmatch pattern.
     *
     * @param  string $pattern The gitignore pattern
     * @return string The fnmatch pattern
     */
    private function gitignoreToFnmatch(string $pattern): string
    {
        // ** matches any path including /
        $pattern = str_replace('**/', '*', $pattern);

        return str_replace('/**', '*', $pattern);
    }
}
