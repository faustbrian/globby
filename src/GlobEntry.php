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
 * access to matched file information including path, name, and stats.
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
     * @param string              $path   Full path to the file/directory
     * @param string              $name   Basename of the file/directory
     * @param null|SplFileInfo    $dirent Directory entry information
     * @param null|GlobEntryStats $stats  File statistics (when stats option enabled)
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
     * @param  string $path         Full path to the file/directory
     * @param  bool   $includeStats Whether to include file statistics
     * @return self   New glob entry instance
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
     */
    public function isFile(): bool
    {
        return $this->dirent?->isFile() ?? is_file($this->path);
    }

    /**
     * Check if this entry represents a directory.
     */
    public function isDirectory(): bool
    {
        return $this->dirent?->isDir() ?? is_dir($this->path);
    }

    /**
     * Check if this entry represents a symbolic link.
     */
    public function isSymbolicLink(): bool
    {
        return $this->dirent?->isLink() ?? is_link($this->path);
    }

    /**
     * Convert to array representation.
     *
     * @return array{path: string, name: string, stats?: array<string, mixed>}
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
