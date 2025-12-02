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
 * Provides access to file metadata like size, timestamps, and permissions.
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
     * @param int               $size           File size in bytes
     * @param DateTimeImmutable $atime          Last access time
     * @param DateTimeImmutable $mtime          Last modification time
     * @param DateTimeImmutable $ctime          Last status change time
     * @param int               $mode           File mode (permissions)
     * @param int               $uid            Owner user ID
     * @param int               $gid            Owner group ID
     * @param int               $ino            Inode number
     * @param int               $nlink          Number of hard links
     * @param bool              $isFile         Whether this is a regular file
     * @param bool              $isDirectory    Whether this is a directory
     * @param bool              $isSymbolicLink Whether this is a symbolic link
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
     * @param string $path Path to the file
     *
     * @throws CannotStatFileException If file stats cannot be retrieved
     *
     * @return self New stats instance
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
     * @return array<string, mixed>
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
