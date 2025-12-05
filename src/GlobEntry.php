<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby;

use SplFileInfo;

use function is_dir;
use function is_file;
use function is_link;

/**
 * Represents a glob match entry with optional file statistics.
 *
 * Similar to Node.js fast-glob Entry interface, provides structured
 * access to matched file information including path, name, directory
 * entry details, and optional file statistics. Used when objectMode
 * is enabled in GlobbyOptions.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class GlobEntry
{
    /**
     * Create a new glob entry.
     *
     * @param string              $path   full path to the file or directory, either absolute or relative
     *                                    depending on the absolute option in GlobbyOptions
     * @param string              $name   basename of the file or directory (filename with extension,
     *                                    or directory name without path components)
     * @param null|SplFileInfo    $dirent SPL file information object providing access to file metadata
     *                                    and type checking methods. Null if not available.
     * @param null|GlobEntryStats $stats  Detailed file statistics including size, timestamps, and permissions.
     *                                    Only populated when stats option is enabled in GlobbyOptions.
     */
    public function __construct(
        public string $path,
        public string $name,
        public ?SplFileInfo $dirent = null,
        public ?GlobEntryStats $stats = null,
    ) {}

    /**
     * Create a glob entry from a file path.
     *
     * Factory method that constructs a GlobEntry from a file system path.
     * Automatically extracts the basename and creates SplFileInfo object.
     * Optionally includes detailed file statistics.
     *
     * @param  string $path         Full path to the file or directory to create entry from
     * @param  bool   $includeStats Whether to include detailed file statistics in the entry
     * @return self   New glob entry instance with populated path, name, dirent, and optional stats
     */
    public static function fromPath(string $path, bool $includeStats = false): self
    {
        $dirent = new SplFileInfo($path);

        return new self(
            path: $path,
            name: $dirent->getBasename(),
            dirent: $dirent,
            stats: $includeStats ? GlobEntryStats::fromPath($path) : null,
        );
    }

    /**
     * Check if this entry represents a file.
     *
     * Uses dirent information if available, otherwise falls back to direct
     * file system check. Returns false for directories and symbolic links.
     *
     * @return bool True if the entry is a regular file
     */
    public function isFile(): bool
    {
        return $this->dirent?->isFile() ?? is_file($this->path);
    }

    /**
     * Check if this entry represents a directory.
     *
     * Uses dirent information if available, otherwise falls back to direct
     * file system check. Returns false for files and symbolic links.
     *
     * @return bool True if the entry is a directory
     */
    public function isDirectory(): bool
    {
        return $this->dirent?->isDir() ?? is_dir($this->path);
    }

    /**
     * Check if this entry represents a symbolic link.
     *
     * Uses dirent information if available, otherwise falls back to direct
     * file system check. Returns true for both file and directory symlinks.
     *
     * @return bool True if the entry is a symbolic link
     */
    public function isSymbolicLink(): bool
    {
        return $this->dirent?->isLink() ?? is_link($this->path);
    }

    /**
     * Convert to array representation.
     *
     * Serializes the entry to an associative array for JSON encoding or
     * other serialization purposes. Stats are included if present and
     * converted to their array representation.
     *
     * @return array{path: string, name: string, stats?: array<string, mixed>} Array with path, name, and optional stats
     */
    public function toArray(): array
    {
        $result = [
            'path' => $this->path,
            'name' => $this->name,
        ];

        if ($this->stats instanceof GlobEntryStats) {
            $result['stats'] = $this->stats->toArray();
        }

        return $result;
    }
}
