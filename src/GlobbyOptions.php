<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby;

use Cline\Globby\Contracts\FileSystemAdapter;

use function array_key_exists;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;

/**
 * Configuration options for Globby operations.
 *
 * Provides a fluent interface for configuring glob matching behavior
 * including directory expansion, gitignore support, and file filtering.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class GlobbyOptions
{
    /**
     * The current working directory for glob operations.
     */
    private ?string $cwd = null;

    /**
     * Whether to automatically expand directories to glob their contents.
     *
     * @var array{files?: array<string>, extensions?: array<string>}|bool
     */
    private array|bool $expandDirectories = true;

    /**
     * Whether to respect .gitignore files.
     */
    private bool $gitignore = false;

    /**
     * Patterns to look for ignore files.
     *
     * @var null|array<string>|string
     */
    private array|string|null $ignoreFiles = null;

    /**
     * Additional patterns to ignore.
     *
     * @var array<string>
     */
    private array $ignore = [];

    /**
     * Whether to match only files (exclude directories).
     */
    private bool $onlyFiles = true;

    /**
     * Whether to match only directories (exclude files).
     */
    private bool $onlyDirectories = false;

    /**
     * Whether to match dotfiles (files starting with .).
     */
    private bool $dot = false;

    /**
     * Maximum depth to traverse.
     */
    private ?int $deep = null;

    /**
     * Whether to follow symbolic links.
     */
    private bool $followSymbolicLinks = true;

    /**
     * Whether to suppress errors when reading files/directories.
     */
    private bool $suppressErrors = false;

    /**
     * Whether to return absolute paths.
     */
    private bool $absolute = false;

    /**
     * Whether to return unique paths only.
     */
    private bool $unique = true;

    /**
     * Whether to mark directories with a trailing slash.
     */
    private bool $markDirectories = false;

    /**
     * Whether to use case-sensitive matching.
     */
    private bool $caseSensitiveMatch = true;

    /**
     * Whether to match patterns against the basename only.
     */
    private bool $baseNameMatch = false;

    /**
     * Custom file system adapter.
     */
    private ?FileSystemAdapter $fs = null;

    /**
     * Whether to throw an error when a broken symbolic link is encountered.
     */
    private bool $throwErrorOnBrokenSymbolicLink = false;

    /**
     * Whether to return GlobEntry objects instead of string paths.
     */
    private bool $objectMode = false;

    /**
     * Whether to include file statistics in GlobEntry objects.
     * Implies objectMode when true.
     */
    private bool $stats = false;

    /**
     * Create a new options instance.
     *
     * @return self New options instance with default values
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Create options from array configuration.
     *
     * @param  array<string, mixed> $options Configuration array
     * @return self                 New options instance
     */
    public static function fromArray(array $options): self
    {
        $instance = new self();

        if (array_key_exists('cwd', $options) && is_string($options['cwd'])) {
            $instance->cwd($options['cwd']);
        }

        if (array_key_exists('expandDirectories', $options)) {
            /** @var array{files?: array<string>, extensions?: array<string>}|bool $expandDirectories */
            $expandDirectories = $options['expandDirectories'];
            $instance->expandDirectories($expandDirectories);
        }

        if (array_key_exists('gitignore', $options) && is_bool($options['gitignore'])) {
            $instance->gitignore($options['gitignore']);
        }

        if (array_key_exists('ignoreFiles', $options)) {
            /** @var array<string>|string $ignoreFiles */
            $ignoreFiles = $options['ignoreFiles'];
            $instance->ignoreFiles($ignoreFiles);
        }

        if (array_key_exists('ignore', $options) && is_array($options['ignore'])) {
            /** @var array<string> $ignore */
            $ignore = $options['ignore'];
            $instance->ignore($ignore);
        }

        if (array_key_exists('onlyFiles', $options) && is_bool($options['onlyFiles'])) {
            $instance->onlyFiles($options['onlyFiles']);
        }

        if (array_key_exists('onlyDirectories', $options) && is_bool($options['onlyDirectories'])) {
            $instance->onlyDirectories($options['onlyDirectories']);
        }

        if (array_key_exists('dot', $options) && is_bool($options['dot'])) {
            $instance->dot($options['dot']);
        }

        if (array_key_exists('deep', $options) && is_int($options['deep'])) {
            $instance->deep($options['deep']);
        }

        if (array_key_exists('followSymbolicLinks', $options) && is_bool($options['followSymbolicLinks'])) {
            $instance->followSymbolicLinks($options['followSymbolicLinks']);
        }

        if (array_key_exists('suppressErrors', $options) && is_bool($options['suppressErrors'])) {
            $instance->suppressErrors($options['suppressErrors']);
        }

        if (array_key_exists('absolute', $options) && is_bool($options['absolute'])) {
            $instance->absolute($options['absolute']);
        }

        if (array_key_exists('unique', $options) && is_bool($options['unique'])) {
            $instance->unique($options['unique']);
        }

        if (array_key_exists('markDirectories', $options) && is_bool($options['markDirectories'])) {
            $instance->markDirectories($options['markDirectories']);
        }

        if (array_key_exists('caseSensitiveMatch', $options) && is_bool($options['caseSensitiveMatch'])) {
            $instance->caseSensitiveMatch($options['caseSensitiveMatch']);
        }

        if (array_key_exists('baseNameMatch', $options) && is_bool($options['baseNameMatch'])) {
            $instance->baseNameMatch($options['baseNameMatch']);
        }

        if (array_key_exists('fs', $options) && $options['fs'] instanceof FileSystemAdapter) {
            $instance->fs($options['fs']);
        }

        if (array_key_exists('throwErrorOnBrokenSymbolicLink', $options) && is_bool($options['throwErrorOnBrokenSymbolicLink'])) {
            $instance->throwErrorOnBrokenSymbolicLink($options['throwErrorOnBrokenSymbolicLink']);
        }

        if (array_key_exists('objectMode', $options) && is_bool($options['objectMode'])) {
            $instance->objectMode($options['objectMode']);
        }

        if (array_key_exists('stats', $options) && is_bool($options['stats'])) {
            $instance->stats($options['stats']);
        }

        return $instance;
    }

    /**
     * Set the current working directory.
     *
     * @param  string $cwd The directory to use as base for glob operations
     * @return $this
     */
    public function cwd(string $cwd): self
    {
        $this->cwd = $cwd;

        return $this;
    }

    /**
     * Get the current working directory.
     *
     * @return null|string The configured cwd, or null to use default
     */
    public function getCwd(): ?string
    {
        return $this->cwd;
    }

    /**
     * Configure directory expansion behavior.
     *
     * When true, directories are automatically expanded with `**\/*`.
     * When an array with 'files' and/or 'extensions', only matching files are included.
     * When false, directory expansion is disabled.
     *
     * @param  array{files?: array<string>, extensions?: array<string>}|bool $value Expansion configuration
     * @return $this
     */
    public function expandDirectories(array|bool $value): self
    {
        $this->expandDirectories = $value;

        return $this;
    }

    /**
     * Get the directory expansion configuration.
     *
     * @return array{files?: array<string>, extensions?: array<string>}|bool
     */
    public function getExpandDirectories(): array|bool
    {
        return $this->expandDirectories;
    }

    /**
     * Enable or disable gitignore support.
     *
     * @param  bool  $value Whether to respect .gitignore files
     * @return $this
     */
    public function gitignore(bool $value = true): self
    {
        $this->gitignore = $value;

        return $this;
    }

    /**
     * Check if gitignore support is enabled.
     *
     * @return bool True if gitignore support is enabled
     */
    public function getGitignore(): bool
    {
        return $this->gitignore;
    }

    /**
     * Set patterns to look for ignore files.
     *
     * @param  array<string>|string $patterns Patterns for finding ignore files
     * @return $this
     */
    public function ignoreFiles(array|string $patterns): self
    {
        $this->ignoreFiles = $patterns;

        return $this;
    }

    /**
     * Get the ignore files patterns.
     *
     * @return null|array<string>|string
     */
    public function getIgnoreFiles(): array|string|null
    {
        return $this->ignoreFiles;
    }

    /**
     * Set additional patterns to ignore.
     *
     * @param  array<string> $patterns Patterns to exclude from results
     * @return $this
     */
    public function ignore(array $patterns): self
    {
        $this->ignore = $patterns;

        return $this;
    }

    /**
     * Get the ignore patterns.
     *
     * @return array<string>
     */
    public function getIgnore(): array
    {
        return $this->ignore;
    }

    /**
     * Set whether to match only files.
     *
     * @param  bool  $value Whether to match only files
     * @return $this
     */
    public function onlyFiles(bool $value = true): self
    {
        $this->onlyFiles = $value;

        if ($value) {
            $this->onlyDirectories = false;
        }

        return $this;
    }

    /**
     * Check if only files should be matched.
     *
     * @return bool True if only files should be matched
     */
    public function getOnlyFiles(): bool
    {
        return $this->onlyFiles;
    }

    /**
     * Set whether to match only directories.
     *
     * @param  bool  $value Whether to match only directories
     * @return $this
     */
    public function onlyDirectories(bool $value = true): self
    {
        $this->onlyDirectories = $value;

        if ($value) {
            $this->onlyFiles = false;
        }

        return $this;
    }

    /**
     * Check if only directories should be matched.
     *
     * @return bool True if only directories should be matched
     */
    public function getOnlyDirectories(): bool
    {
        return $this->onlyDirectories;
    }

    /**
     * Set whether to match dotfiles.
     *
     * @param  bool  $value whether to include files starting with
     * @return $this
     */
    public function dot(bool $value = true): self
    {
        $this->dot = $value;

        return $this;
    }

    /**
     * Check if dotfiles should be matched.
     *
     * @return bool True if dotfiles should be matched
     */
    public function getDot(): bool
    {
        return $this->dot;
    }

    /**
     * Set maximum traversal depth.
     *
     * @param  int   $depth Maximum directory depth to traverse
     * @return $this
     */
    public function deep(int $depth): self
    {
        $this->deep = $depth;

        return $this;
    }

    /**
     * Get the maximum traversal depth.
     *
     * @return null|int The configured depth limit, or null for unlimited
     */
    public function getDeep(): ?int
    {
        return $this->deep;
    }

    /**
     * Set whether to follow symbolic links.
     *
     * @param  bool  $value Whether to follow symlinks
     * @return $this
     */
    public function followSymbolicLinks(bool $value = true): self
    {
        $this->followSymbolicLinks = $value;

        return $this;
    }

    /**
     * Check if symbolic links should be followed.
     *
     * @return bool True if symlinks should be followed
     */
    public function getFollowSymbolicLinks(): bool
    {
        return $this->followSymbolicLinks;
    }

    /**
     * Set whether to suppress errors.
     *
     * @param  bool  $value Whether to suppress file system errors
     * @return $this
     */
    public function suppressErrors(bool $value = true): self
    {
        $this->suppressErrors = $value;

        return $this;
    }

    /**
     * Check if errors should be suppressed.
     *
     * @return bool True if errors should be suppressed
     */
    public function getSuppressErrors(): bool
    {
        return $this->suppressErrors;
    }

    /**
     * Set whether to return absolute paths.
     *
     * @param  bool  $value Whether to return absolute paths
     * @return $this
     */
    public function absolute(bool $value = true): self
    {
        $this->absolute = $value;

        return $this;
    }

    /**
     * Check if absolute paths should be returned.
     *
     * @return bool True if absolute paths should be returned
     */
    public function getAbsolute(): bool
    {
        return $this->absolute;
    }

    /**
     * Set whether to return unique paths only.
     *
     * @param  bool  $value Whether to deduplicate results
     * @return $this
     */
    public function unique(bool $value = true): self
    {
        $this->unique = $value;

        return $this;
    }

    /**
     * Check if unique paths only should be returned.
     *
     * @return bool True if results should be deduplicated
     */
    public function getUnique(): bool
    {
        return $this->unique;
    }

    /**
     * Set whether to mark directories with a trailing slash.
     *
     * @param  bool  $value Whether to add trailing slash to directories
     * @return $this
     */
    public function markDirectories(bool $value = true): self
    {
        $this->markDirectories = $value;

        return $this;
    }

    /**
     * Check if directories should be marked with trailing slash.
     *
     * @return bool True if directories should have trailing slash
     */
    public function getMarkDirectories(): bool
    {
        return $this->markDirectories;
    }

    /**
     * Set whether to use case-sensitive matching.
     *
     * @param  bool  $value Whether matching should be case-sensitive
     * @return $this
     */
    public function caseSensitiveMatch(bool $value = true): self
    {
        $this->caseSensitiveMatch = $value;

        return $this;
    }

    /**
     * Check if case-sensitive matching is enabled.
     *
     * @return bool True if matching is case-sensitive
     */
    public function getCaseSensitiveMatch(): bool
    {
        return $this->caseSensitiveMatch;
    }

    /**
     * Set whether to match patterns against basename only.
     *
     * @param  bool  $value Whether to match basename only
     * @return $this
     */
    public function baseNameMatch(bool $value = true): self
    {
        $this->baseNameMatch = $value;

        return $this;
    }

    /**
     * Check if basename-only matching is enabled.
     *
     * @return bool True if matching against basename only
     */
    public function getBaseNameMatch(): bool
    {
        return $this->baseNameMatch;
    }

    /**
     * Set custom file system adapter.
     *
     * @param  FileSystemAdapter $fs Custom file system implementation
     * @return $this
     */
    public function fs(FileSystemAdapter $fs): self
    {
        $this->fs = $fs;

        return $this;
    }

    /**
     * Get the file system adapter.
     *
     * @return null|FileSystemAdapter The configured adapter, or null for default
     */
    public function getFs(): ?FileSystemAdapter
    {
        return $this->fs;
    }

    /**
     * Set whether to throw an error on broken symbolic links.
     *
     * @param  bool  $value Whether to throw on broken symlinks
     * @return $this
     */
    public function throwErrorOnBrokenSymbolicLink(bool $value = true): self
    {
        $this->throwErrorOnBrokenSymbolicLink = $value;

        return $this;
    }

    /**
     * Check if errors should be thrown on broken symbolic links.
     *
     * @return bool True if errors should be thrown
     */
    public function getThrowErrorOnBrokenSymbolicLink(): bool
    {
        return $this->throwErrorOnBrokenSymbolicLink;
    }

    /**
     * Set whether to return GlobEntry objects instead of strings.
     *
     * @param  bool  $value Whether to use object mode
     * @return $this
     */
    public function objectMode(bool $value = true): self
    {
        $this->objectMode = $value;

        return $this;
    }

    /**
     * Check if object mode is enabled.
     *
     * @return bool True if GlobEntry objects should be returned
     */
    public function getObjectMode(): bool
    {
        return $this->objectMode || $this->stats;
    }

    /**
     * Set whether to include file statistics.
     *
     * @param  bool  $value Whether to include stats
     * @return $this
     */
    public function stats(bool $value = true): self
    {
        $this->stats = $value;

        if ($value) {
            $this->objectMode = true;
        }

        return $this;
    }

    /**
     * Check if file statistics should be included.
     *
     * @return bool True if stats should be included
     */
    public function getStats(): bool
    {
        return $this->stats;
    }

    /**
     * Convert options to array format compatible with array-based configuration.
     *
     * @return array<string, mixed> Options as associative array
     */
    public function toArray(): array
    {
        return [
            'cwd' => $this->cwd,
            'expandDirectories' => $this->expandDirectories,
            'gitignore' => $this->gitignore,
            'ignoreFiles' => $this->ignoreFiles,
            'ignore' => $this->ignore,
            'onlyFiles' => $this->onlyFiles,
            'onlyDirectories' => $this->onlyDirectories,
            'dot' => $this->dot,
            'deep' => $this->deep,
            'followSymbolicLinks' => $this->followSymbolicLinks,
            'suppressErrors' => $this->suppressErrors,
            'absolute' => $this->absolute,
            'unique' => $this->unique,
            'markDirectories' => $this->markDirectories,
            'caseSensitiveMatch' => $this->caseSensitiveMatch,
            'baseNameMatch' => $this->baseNameMatch,
            'fs' => $this->fs,
            'throwErrorOnBrokenSymbolicLink' => $this->throwErrorOnBrokenSymbolicLink,
            'objectMode' => $this->objectMode,
            'stats' => $this->stats,
        ];
    }
}
