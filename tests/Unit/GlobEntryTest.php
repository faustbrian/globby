<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\GlobEntry;
use Cline\Globby\GlobEntryStats;

describe('GlobEntry', function (): void {
    describe('Constructor', function (): void {
        test('creates entry with all parameters', function (): void {
            $path = $this->fixturePath('unicorn.txt');
            $dirent = new SplFileInfo($path);
            $stats = GlobEntryStats::fromPath($path);

            $entry = new GlobEntry(
                path: $path,
                name: 'unicorn.txt',
                dirent: $dirent,
                stats: $stats,
            );

            expect($entry->path)->toBe($path);
            expect($entry->name)->toBe('unicorn.txt');
            expect($entry->dirent)->toBe($dirent);
            expect($entry->stats)->toBe($stats);
        });

        test('creates entry with minimal parameters', function (): void {
            $path = $this->fixturePath('unicorn.txt');

            $entry = new GlobEntry(
                path: $path,
                name: 'unicorn.txt',
            );

            expect($entry->path)->toBe($path);
            expect($entry->name)->toBe('unicorn.txt');
            expect($entry->dirent)->toBeNull();
            expect($entry->stats)->toBeNull();
        });
    });

    describe('fromPath()', function (): void {
        test('creates entry from real file path', function (): void {
            $path = $this->fixturePath('unicorn.txt');

            $entry = GlobEntry::fromPath($path);

            expect($entry->path)->toBe($path);
            expect($entry->name)->toBe('unicorn.txt');
            expect($entry->dirent)->toBeInstanceOf(SplFileInfo::class);
            expect($entry->stats)->toBeNull();
        });

        test('creates entry with stats when includeStats is true', function (): void {
            $path = $this->fixturePath('cake.txt');

            $entry = GlobEntry::fromPath($path, includeStats: true);

            expect($entry->path)->toBe($path);
            expect($entry->name)->toBe('cake.txt');
            expect($entry->dirent)->toBeInstanceOf(SplFileInfo::class);
            expect($entry->stats)->toBeInstanceOf(GlobEntryStats::class);
        });

        test('creates entry without stats when includeStats is false', function (): void {
            $path = $this->fixturePath('rainbow.txt');

            $entry = GlobEntry::fromPath($path, includeStats: false);

            expect($entry->path)->toBe($path);
            expect($entry->name)->toBe('rainbow.txt');
            expect($entry->dirent)->toBeInstanceOf(SplFileInfo::class);
            expect($entry->stats)->toBeNull();
        });

        test('creates entry for directory', function (): void {
            $path = $this->fixturePath('nested');

            $entry = GlobEntry::fromPath($path);

            expect($entry->path)->toBe($path);
            expect($entry->name)->toBe('nested');
            expect($entry->dirent)->toBeInstanceOf(SplFileInfo::class);
        });

        test('creates entry for hidden file', function (): void {
            $path = $this->fixturePath('.hidden');

            $entry = GlobEntry::fromPath($path);

            expect($entry->path)->toBe($path);
            expect($entry->name)->toBe('.hidden');
            expect($entry->dirent)->toBeInstanceOf(SplFileInfo::class);
        });
    });

    describe('isFile()', function (): void {
        test('returns true for regular files', function (): void {
            $path = $this->fixturePath('unicorn.txt');
            $entry = GlobEntry::fromPath($path);

            expect($entry->isFile())->toBeTrue();
        });

        test('returns false for directories', function (): void {
            $path = $this->fixturePath('nested');
            $entry = GlobEntry::fromPath($path);

            expect($entry->isFile())->toBeFalse();
        });

        test('returns true when dirent is null and path is a file', function (): void {
            $path = $this->fixturePath('cake.txt');

            $entry = new GlobEntry(
                path: $path,
                name: 'cake.txt',
            );

            expect($entry->isFile())->toBeTrue();
        });
    });

    describe('isDirectory()', function (): void {
        test('returns true for directories', function (): void {
            $path = $this->fixturePath('nested');
            $entry = GlobEntry::fromPath($path);

            expect($entry->isDirectory())->toBeTrue();
        });

        test('returns false for regular files', function (): void {
            $path = $this->fixturePath('unicorn.txt');
            $entry = GlobEntry::fromPath($path);

            expect($entry->isDirectory())->toBeFalse();
        });

        test('returns true when dirent is null and path is a directory', function (): void {
            $path = $this->fixturePath('nested');

            $entry = new GlobEntry(
                path: $path,
                name: 'nested',
            );

            expect($entry->isDirectory())->toBeTrue();
        });
    });

    describe('isSymbolicLink()', function (): void {
        test('returns false for regular files', function (): void {
            $path = $this->fixturePath('unicorn.txt');
            $entry = GlobEntry::fromPath($path);

            expect($entry->isSymbolicLink())->toBeFalse();
        });

        test('returns false for directories', function (): void {
            $path = $this->fixturePath('nested');
            $entry = GlobEntry::fromPath($path);

            expect($entry->isSymbolicLink())->toBeFalse();
        });

        test('returns false when dirent is null and path is not a symlink', function (): void {
            $path = $this->fixturePath('cake.txt');

            $entry = new GlobEntry(
                path: $path,
                name: 'cake.txt',
            );

            expect($entry->isSymbolicLink())->toBeFalse();
        });
    });

    describe('toArray()', function (): void {
        test('returns correct structure without stats', function (): void {
            $path = $this->fixturePath('unicorn.txt');
            $entry = GlobEntry::fromPath($path);

            $array = $entry->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKey('path');
            expect($array)->toHaveKey('name');
            expect($array)->not->toHaveKey('stats');
            expect($array['path'])->toBe($path);
            expect($array['name'])->toBe('unicorn.txt');
        });

        test('includes stats array when stats present', function (): void {
            $path = $this->fixturePath('cake.txt');
            $entry = GlobEntry::fromPath($path, includeStats: true);

            $array = $entry->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKey('path');
            expect($array)->toHaveKey('name');
            expect($array)->toHaveKey('stats');
            expect($array['path'])->toBe($path);
            expect($array['name'])->toBe('cake.txt');
            expect($array['stats'])->toBeArray();
            expect($array['stats'])->toHaveKey('size');
            expect($array['stats'])->toHaveKey('mtime');
            expect($array['stats'])->toHaveKey('atime');
            expect($array['stats'])->toHaveKey('ctime');
            expect($array['stats'])->toHaveKey('mode');
            expect($array['stats'])->toHaveKey('uid');
            expect($array['stats'])->toHaveKey('gid');
            expect($array['stats'])->toHaveKey('ino');
            expect($array['stats'])->toHaveKey('nlink');
            expect($array['stats'])->toHaveKey('isFile');
            expect($array['stats'])->toHaveKey('isDirectory');
            expect($array['stats'])->toHaveKey('isSymbolicLink');
        });

        test('does not include stats key when stats is null', function (): void {
            $path = $this->fixturePath('rainbow.txt');
            $entry = GlobEntry::fromPath($path, includeStats: false);

            $array = $entry->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKeys(['path', 'name']);
            expect($array)->not->toHaveKey('stats');
        });
    });
});
