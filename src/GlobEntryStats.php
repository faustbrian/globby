<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby;

use Carbon\CarbonImmutable;
use Cline\Globby\Exceptions\CannotStatFileException;
use DateTimeImmutable;

use function is_dir;
use function is_file;
use function is_link;
use function restore_error_handler;
use function set_error_handler;
use function stat;

/**
 * File statistics for a glob entry.
 *
 * Provides access to file metadata like size, timestamps, permissions,
 * ownership, and inode information. Closely mirrors PHP's stat() output
 * with convenient DateTime objects for timestamps and boolean flags for
 * common file type checks.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class GlobEntryStats
{
    /**
     * Create a new stats instance.
     *
     * @param int               $size           File size in bytes. For directories, this may be
     *                                          system-dependent (often 4096 or block size).
     * @param DateTimeImmutable $atime          Last access time - when the file was last read or
     *                                          accessed. May be disabled on some filesystems for performance.
     * @param DateTimeImmutable $mtime          Last modification time - when the file content was
     *                                          last changed. Used for cache invalidation and versioning.
     * @param DateTimeImmutable $ctime          Last status change time - when file metadata (permissions,
     *                                          ownership, etc.) was changed. Not creation time on Unix systems.
     * @param int               $mode           File mode (permissions and type). Includes both permission bits
     *                                          and file type bits. Use with permission constants like 0755.
     * @param int               $uid            Owner user ID. Numeric user identifier of the file owner
     *                                          on Unix-like systems.
     * @param int               $gid            Owner group ID. Numeric group identifier of the file's
     *                                          owning group on Unix-like systems.
     * @param int               $ino            Inode number. Unique identifier for the file within
     *                                          the filesystem. Two files with same inode are hard links.
     * @param int               $nlink          Number of hard links. Count of directory entries pointing
     *                                          to this inode. Minimum 1, higher for hard-linked files.
     * @param bool              $isFile         Whether this is a regular file (not directory, symlink, etc.)
     * @param bool              $isDirectory    Whether this is a directory
     * @param bool              $isSymbolicLink Whether this is a symbolic link (even if pointing to file/directory)
     */
    public function __construct(
        public int $size,
        public DateTimeImmutable $atime,
        public DateTimeImmutable $mtime,
        public DateTimeImmutable $ctime,
        public int $mode,
        public int $uid,
        public int $gid,
        public int $ino,
        public int $nlink,
        public bool $isFile,
        public bool $isDirectory,
        public bool $isSymbolicLink,
    ) {}

    /**
     * Create stats from a file path.
     *
     * Factory method that retrieves file statistics using PHP's stat() function
     * and constructs a GlobEntryStats instance. Errors during stat() are suppressed
     * and converted to CannotStatFileException.
     *
     * @param string $path Path to the file or directory to retrieve statistics for
     *
     * @throws CannotStatFileException If file stats cannot be retrieved (file doesn't exist, permission denied, etc.)
     *
     * @return self New stats instance populated with file system metadata
     */
    public static function fromPath(string $path): self
    {
        set_error_handler(static fn (): bool => true);

        try {
            $stat = stat($path);
        } finally {
            restore_error_handler();
        }

        if ($stat === false) {
            throw CannotStatFileException::forPath($path);
        }

        return new self(
            size: $stat['size'],
            atime: CarbonImmutable::now()->setTimestamp($stat['atime']),
            mtime: CarbonImmutable::now()->setTimestamp($stat['mtime']),
            ctime: CarbonImmutable::now()->setTimestamp($stat['ctime']),
            mode: $stat['mode'],
            uid: $stat['uid'],
            gid: $stat['gid'],
            ino: $stat['ino'],
            nlink: $stat['nlink'],
            isFile: is_file($path),
            isDirectory: is_dir($path),
            isSymbolicLink: is_link($path),
        );
    }

    /**
     * Convert to array representation.
     *
     * Serializes all statistics to an associative array for JSON encoding
     * or other serialization. Timestamps are converted to Unix timestamps.
     *
     * @return array<string, mixed> Array containing all stats with timestamps as Unix seconds
     */
    public function toArray(): array
    {
        return [
            'size' => $this->size,
            'atime' => $this->atime->getTimestamp(),
            'mtime' => $this->mtime->getTimestamp(),
            'ctime' => $this->ctime->getTimestamp(),
            'mode' => $this->mode,
            'uid' => $this->uid,
            'gid' => $this->gid,
            'ino' => $this->ino,
            'nlink' => $this->nlink,
            'isFile' => $this->isFile,
            'isDirectory' => $this->isDirectory,
            'isSymbolicLink' => $this->isSymbolicLink,
        ];
    }
}
