<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\Support\NativeFileSystem;

describe('NativeFileSystem', function (): void {
    beforeEach(function (): void {
        $this->fs = new NativeFileSystem();
    });

    describe('exists', function (): void {
        test('returns true for existing file', function (): void {
            expect($this->fs->exists($this->fixturePath('unicorn.txt')))->toBeTrue();
        });

        test('returns true for existing directory', function (): void {
            expect($this->fs->exists($this->fixturePath('nested')))->toBeTrue();
        });

        test('returns false for non-existent path', function (): void {
            expect($this->fs->exists('/non/existent/path/that/does/not/exist'))->toBeFalse();
        });

        test('returns true for hidden files', function (): void {
            expect($this->fs->exists($this->fixturePath('.hidden')))->toBeTrue();
        });
    });

    describe('isDirectory', function (): void {
        test('returns true for directory', function (): void {
            expect($this->fs->isDirectory($this->fixturePath('nested')))->toBeTrue();
        });

        test('returns false for file', function (): void {
            expect($this->fs->isDirectory($this->fixturePath('unicorn.txt')))->toBeFalse();
        });

        test('returns false for non-existent path', function (): void {
            expect($this->fs->isDirectory('/non/existent/directory'))->toBeFalse();
        });

        test('returns true for nested directory', function (): void {
            expect($this->fs->isDirectory($this->fixturePath('nested/deep')))->toBeTrue();
        });
    });

    describe('isFile', function (): void {
        test('returns true for file', function (): void {
            expect($this->fs->isFile($this->fixturePath('unicorn.txt')))->toBeTrue();
        });

        test('returns false for directory', function (): void {
            expect($this->fs->isFile($this->fixturePath('nested')))->toBeFalse();
        });

        test('returns false for non-existent path', function (): void {
            expect($this->fs->isFile('/non/existent/file.txt'))->toBeFalse();
        });

        test('returns true for hidden file', function (): void {
            expect($this->fs->isFile($this->fixturePath('.hidden')))->toBeTrue();
        });
    });

    describe('readFile', function (): void {
        test('reads file contents', function (): void {
            $contents = $this->fs->readFile($this->fixturePath('.gitignore'));

            expect($contents)->toBeString();
            expect($contents)->toContain('cake.txt');
        });

        test('returns empty string for non-existent file', function (): void {
            $contents = $this->fs->readFile('/non/existent/file.txt');

            expect($contents)->toBe('');
        });

        test('reads empty file', function (): void {
            $contents = $this->fs->readFile($this->fixturePath('cake.txt'));

            expect($contents)->toBe('');
        });

        test('reads hidden file', function (): void {
            $contents = $this->fs->readFile($this->fixturePath('.hidden'));

            expect($contents)->toBe('');
        });
    });

    describe('glob', function (): void {
        test('finds files matching pattern', function (): void {
            $results = $this->fs->glob($this->fixturePath('*.txt'));

            expect($results)->toBeArray();
            expect($results)->not->toBeEmpty();
            expect($results)->toContain($this->fixturePath('unicorn.txt'));
        });

        test('returns empty array for no matches', function (): void {
            $results = $this->fs->glob($this->fixturePath('*.xyz'));

            expect($results)->toBe([]);
        });

        test('finds files in nested directories', function (): void {
            $results = $this->fs->glob($this->fixturePath('nested/*'));

            expect($results)->toBeArray();
            expect($results)->not->toBeEmpty();
        });

        test('supports GLOB_ONLYDIR flag', function (): void {
            $results = $this->fs->glob($this->fixturePath('*'), \GLOB_ONLYDIR);

            expect($results)->toBeArray();

            foreach ($results as $path) {
                expect(is_dir($path))->toBeTrue();
            }
        });

        test('returns empty array for invalid pattern', function (): void {
            $results = $this->fs->glob('');

            expect($results)->toBe([]);
        });
    });

    describe('realpath', function (): void {
        test('returns absolute path for existing file', function (): void {
            $result = $this->fs->realpath($this->fixturePath('unicorn.txt'));

            expect($result)->toBeString();
            expect($result)->toContain('unicorn.txt');
            expect($result[0] ?? '')->toBe('/');
        });

        test('returns false for non-existent path', function (): void {
            $result = $this->fs->realpath('/non/existent/path');

            expect($result)->toBeFalse();
        });

        test('resolves relative paths', function (): void {
            $result = $this->fs->realpath($this->fixturePath('.'));

            expect($result)->toBeString();
            expect($result)->toContain('Fixtures');
        });

        test('resolves paths with parent directory references', function (): void {
            $result = $this->fs->realpath($this->fixturePath('nested/../unicorn.txt'));

            expect($result)->toBeString();
            expect($result)->toContain('unicorn.txt');
            expect(str_contains($result, '..'))->toBeFalse();
        });
    });

    describe('getcwd', function (): void {
        test('returns current working directory', function (): void {
            $result = $this->fs->getcwd();

            expect($result)->toBeString();
            expect($result)->not->toBeEmpty();
            expect($result[0] ?? '')->toBe('/');
        });

        test('returns string matching PHP getcwd', function (): void {
            $expected = getcwd();
            $result = $this->fs->getcwd();

            if ($expected !== false) {
                expect($result)->toBe($expected);
            } else {
                expect($result)->toBe('');
            }
        });
    });
});
