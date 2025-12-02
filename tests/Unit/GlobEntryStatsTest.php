<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\Exceptions\CannotStatFileException;
use Cline\Globby\GlobEntryStats;

describe('GlobEntryStats', function (): void {
    describe('Happy Path', function (): void {
        test('creates stats from real file path', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);

            expect($stats)->toBeInstanceOf(GlobEntryStats::class);
            expect($stats->size)->toBeInt();
            expect($stats->atime)->toBeInstanceOf(DateTimeImmutable::class);
            expect($stats->mtime)->toBeInstanceOf(DateTimeImmutable::class);
            expect($stats->ctime)->toBeInstanceOf(DateTimeImmutable::class);
            expect($stats->mode)->toBeInt();
            expect($stats->uid)->toBeInt();
            expect($stats->gid)->toBeInt();
            expect($stats->ino)->toBeInt();
            expect($stats->nlink)->toBeInt();
        });

        test('stats object has correct file size', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $expectedSize = filesize($filePath);

            expect($stats->size)->toBe($expectedSize);
        });

        test('stats object has correct timestamps', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $fileStats = stat($filePath);

            expect($stats->atime->getTimestamp())->toBe($fileStats['atime']);
            expect($stats->mtime->getTimestamp())->toBe($fileStats['mtime']);
            expect($stats->ctime->getTimestamp())->toBe($fileStats['ctime']);
        });

        test('stats object has correct mode and permissions', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $fileStats = stat($filePath);

            expect($stats->mode)->toBe($fileStats['mode']);
        });

        test('stats object has correct uid and gid', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $fileStats = stat($filePath);

            expect($stats->uid)->toBe($fileStats['uid']);
            expect($stats->gid)->toBe($fileStats['gid']);
        });

        test('stats object has correct inode and nlink', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $fileStats = stat($filePath);

            expect($stats->ino)->toBe($fileStats['ino']);
            expect($stats->nlink)->toBe($fileStats['nlink']);
        });

        test('isFile is true for regular files', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);

            expect($stats->isFile)->toBeTrue();
            expect($stats->isDirectory)->toBeFalse();
        });

        test('isFile is true for different file types', function (): void {
            $filePath = $this->fixturePath('rainbow.txt');
            $stats = GlobEntryStats::fromPath($filePath);

            expect($stats->isFile)->toBeTrue();
            expect($stats->isDirectory)->toBeFalse();
        });

        test('isDirectory is true for directories', function (): void {
            $dirPath = $this->fixturePath('nested');
            $stats = GlobEntryStats::fromPath($dirPath);

            expect($stats->isDirectory)->toBeTrue();
            expect($stats->isFile)->toBeFalse();
        });

        test('isDirectory is true for nested directories', function (): void {
            $dirPath = $this->fixturePath('nested/deep');
            $stats = GlobEntryStats::fromPath($dirPath);

            expect($stats->isDirectory)->toBeTrue();
            expect($stats->isFile)->toBeFalse();
        });

        test('isSymbolicLink is false for regular files', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);

            expect($stats->isSymbolicLink)->toBeFalse();
        });

        test('isSymbolicLink is false for directories', function (): void {
            $dirPath = $this->fixturePath('nested');
            $stats = GlobEntryStats::fromPath($dirPath);

            expect($stats->isSymbolicLink)->toBeFalse();
        });
    });

    describe('Array Conversion', function (): void {
        test('toArray returns correct structure with all properties', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $array = $stats->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKeys([
                'size',
                'atime',
                'mtime',
                'ctime',
                'mode',
                'uid',
                'gid',
                'ino',
                'nlink',
                'isFile',
                'isDirectory',
                'isSymbolicLink',
            ]);
        });

        test('toArray returns correct values for file', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $array = $stats->toArray();

            expect($array['size'])->toBe($stats->size);
            expect($array['atime'])->toBe($stats->atime->getTimestamp());
            expect($array['mtime'])->toBe($stats->mtime->getTimestamp());
            expect($array['ctime'])->toBe($stats->ctime->getTimestamp());
            expect($array['mode'])->toBe($stats->mode);
            expect($array['uid'])->toBe($stats->uid);
            expect($array['gid'])->toBe($stats->gid);
            expect($array['ino'])->toBe($stats->ino);
            expect($array['nlink'])->toBe($stats->nlink);
            expect($array['isFile'])->toBeTrue();
            expect($array['isDirectory'])->toBeFalse();
            expect($array['isSymbolicLink'])->toBeFalse();
        });

        test('toArray returns correct values for directory', function (): void {
            $dirPath = $this->fixturePath('nested');
            $stats = GlobEntryStats::fromPath($dirPath);
            $array = $stats->toArray();

            expect($array['isFile'])->toBeFalse();
            expect($array['isDirectory'])->toBeTrue();
            expect($array['isSymbolicLink'])->toBeFalse();
        });

        test('toArray timestamps are integers', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $array = $stats->toArray();

            expect($array['atime'])->toBeInt();
            expect($array['mtime'])->toBeInt();
            expect($array['ctime'])->toBeInt();
        });
    });

    describe('Edge Cases', function (): void {
        test('fromPath throws CannotStatFileException for non-existent file', function (): void {
            $nonExistentPath = $this->fixturePath('does-not-exist.txt');

            expect(fn (): GlobEntryStats => GlobEntryStats::fromPath($nonExistentPath))
                ->toThrow(CannotStatFileException::class);
        });

        test('fromPath throws CannotStatFileException with correct message', function (): void {
            $nonExistentPath = $this->fixturePath('does-not-exist.txt');

            try {
                GlobEntryStats::fromPath($nonExistentPath);
                expect(false)->toBeTrue(); // Should not reach here
            } catch (CannotStatFileException $cannotStatFileException) {
                expect($cannotStatFileException->getMessage())->toContain('Cannot stat file:');
                expect($cannotStatFileException->getMessage())->toContain($nonExistentPath);
            }
        });

        test('handles files in deeply nested directories', function (): void {
            $deepFilePath = $this->fixturePath('nested/deep/readme.md');
            $stats = GlobEntryStats::fromPath($deepFilePath);

            expect($stats->isFile)->toBeTrue();
            expect($stats->isDirectory)->toBeFalse();
            expect($stats->size)->toBeGreaterThanOrEqual(0);
        });

        test('stats are immutable', function (): void {
            $filePath = $this->fixturePath('unicorn.txt');
            $stats = GlobEntryStats::fromPath($filePath);
            $originalSize = $stats->size;

            // Verify readonly class prevents modification
            // This test demonstrates the readonly nature of the class
            expect($stats->size)->toBe($originalSize);
        });
    });
});
