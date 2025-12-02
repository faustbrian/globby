<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\Exceptions\DirectoryNotFoundException;
use Cline\Globby\Facades\Globby;
use Cline\Globby\GlobbyManager;
use Cline\Globby\GlobEntry;
use Cline\Globby\Support\NativeFileSystem;

describe('GlobbyManager', function (): void {
    describe('Happy Path', function (): void {
        test('matches single pattern', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', ['cwd' => $this->fixturePath()]);

            expect($results)->toBeArray();
            expect($results)->toContain('unicorn.txt');
            expect($results)->toContain('cake.txt');
            expect($results)->toContain('rainbow.txt');
        });

        test('matches multiple patterns', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob(['*.txt', 'nested/*.php'], ['cwd' => $this->fixturePath()]);

            expect($results)->toContain('unicorn.txt');
            expect($results)->toContain('nested/file1.php');
        });

        test('supports negation patterns', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob(['*.txt', '!cake.txt'], ['cwd' => $this->fixturePath()]);

            expect($results)->toContain('unicorn.txt');
            expect($results)->toContain('rainbow.txt');
            expect($results)->not->toContain('cake.txt');
        });

        test('supports negation-only patterns', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob(['!*.txt'], ['cwd' => $this->fixturePath(), 'expandDirectories' => false]);

            // Should match all non-txt files
            expect($results)->not->toContain('unicorn.txt');
            expect($results)->not->toContain('cake.txt');
        });

        test('expands directories automatically', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('nested', ['cwd' => $this->fixturePath()]);

            expect($results)->toContain('nested/file1.php');
            expect($results)->toContain('nested/file2.php');
            expect($results)->toContain('nested/deep/secret.txt');
        });

        test('expands directories with specific extensions', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('nested', [
                'cwd' => $this->fixturePath(),
                'expandDirectories' => [
                    'extensions' => ['php'],
                ],
            ]);

            expect($results)->toContain('nested/file1.php');
            expect($results)->not->toContain('nested/file3.js');
        });

        test('expands directories with specific files', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('nested', [
                'cwd' => $this->fixturePath(),
                'expandDirectories' => [
                    'files' => ['file1.php'],
                ],
            ]);

            expect($results)->toContain('nested/file1.php');
            expect($results)->not->toContain('nested/file2.php');
        });

        test('expands directories with both files and extensions', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('nested', [
                'cwd' => $this->fixturePath(),
                'expandDirectories' => [
                    'files' => ['secret.txt'],
                    'extensions' => ['md'],
                ],
            ]);

            expect($results)->toContain('nested/deep/secret.txt');
            expect($results)->toContain('nested/deep/readme.md');
            expect($results)->not->toContain('nested/file1.php');
        });

        test('respects gitignore when enabled', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'gitignore' => true,
            ]);

            expect($results)->toContain('unicorn.txt');
            expect($results)->not->toContain('cake.txt');
        });

        test('matches recursive patterns with **', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('**/*.txt', ['cwd' => $this->fixturePath()]);

            expect($results)->toContain('unicorn.txt');
            expect($results)->toContain('nested/deep/secret.txt');
        });

        test('matches files by extension', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('**/*.md', ['cwd' => $this->fixturePath()]);

            expect($results)->toContain('nested/deep/readme.md');
            expect($results)->toContain('docs/guide.md');
        });

        test('includes dotfiles when dot option enabled', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*', [
                'cwd' => $this->fixturePath(),
                'dot' => true,
                'onlyFiles' => true,
            ]);

            expect($results)->toContain('.hidden');
        });

        test('excludes dotfiles by default', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*', [
                'cwd' => $this->fixturePath(),
                'onlyFiles' => true,
            ]);

            expect($results)->not->toContain('.hidden');
        });

        test('returns absolute paths when requested', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'absolute' => true,
            ]);

            foreach ($results as $path) {
                expect(str_starts_with($path, \DIRECTORY_SEPARATOR))->toBeTrue();
            }
        });

        test('returns relative paths by default', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
            ]);

            foreach ($results as $path) {
                expect(str_starts_with($path, \DIRECTORY_SEPARATOR))->toBeFalse();
            }
        });

        test('returns sorted results', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', ['cwd' => $this->fixturePath()]);

            $sorted = $results;
            sort($sorted);

            expect($results)->toBe($sorted);
        });

        test('streams results with generator', function (): void {
            $manager = new GlobbyManager();
            $generator = $manager->stream('*.txt', ['cwd' => $this->fixturePath()]);

            expect($generator)->toBeInstanceOf(Generator::class);

            $results = iterator_to_array($generator);
            expect($results)->not->toBeEmpty();
        });
    });

    describe('Pattern Detection', function (): void {
        test('detects dynamic patterns with asterisk', function (): void {
            $manager = new GlobbyManager();

            expect($manager->isDynamicPattern('*.txt'))->toBeTrue();
            expect($manager->isDynamicPattern('foo*bar'))->toBeTrue();
        });

        test('detects dynamic patterns with globstar', function (): void {
            $manager = new GlobbyManager();

            expect($manager->isDynamicPattern('**/*.txt'))->toBeTrue();
        });

        test('detects dynamic patterns with question mark', function (): void {
            $manager = new GlobbyManager();

            expect($manager->isDynamicPattern('file?.txt'))->toBeTrue();
        });

        test('detects dynamic patterns with braces', function (): void {
            $manager = new GlobbyManager();

            expect($manager->isDynamicPattern('{foo,bar}.txt'))->toBeTrue();
            expect($manager->isDynamicPattern('[abc].txt'))->toBeTrue();
        });

        test('identifies static patterns', function (): void {
            $manager = new GlobbyManager();

            expect($manager->isDynamicPattern('file.txt'))->toBeFalse();
            expect($manager->isDynamicPattern('path/to/file.txt'))->toBeFalse();
        });
    });

    describe('Path Conversion', function (): void {
        test('escapes glob characters in paths', function (): void {
            $manager = new GlobbyManager();

            expect($manager->convertPathToPattern('file[1].txt'))->toBe('file\\[1\\].txt');
            expect($manager->convertPathToPattern('(test).txt'))->toBe('\\(test\\).txt');
            expect($manager->convertPathToPattern('file{a,b}.txt'))->toBe('file\\{a,b\\}.txt');
        });

        test('converts backslashes to forward slashes', function (): void {
            $manager = new GlobbyManager();

            $result = $manager->convertPathToPattern('path\\to\\file.txt');
            expect(str_contains($result, '\\\\'))->toBeFalse();
        });
    });

    describe('Glob Tasks', function (): void {
        test('generates glob tasks from patterns', function (): void {
            $manager = new GlobbyManager();
            $tasks = $manager->generateGlobTasks(['*.txt', '!cake.txt'], ['cwd' => $this->fixturePath()]);

            expect($tasks)->toBeArray();
            expect($tasks)->toHaveCount(1);
            expect($tasks[0])->toHaveKey('patterns');
            expect($tasks[0])->toHaveKey('options');
        });

        test('separates positive and negative patterns in tasks', function (): void {
            $manager = new GlobbyManager();
            $tasks = $manager->generateGlobTasks(['*.txt', '!cake.txt'], ['cwd' => $this->fixturePath()]);

            expect($tasks[0]['options']['negative'])->toContain('cake.txt');
        });

        test('generates glob tasks with only negative patterns by prepending **/*', function (): void {
            $manager = new GlobbyManager();
            $tasks = $manager->generateGlobTasks(['!*.log'], ['cwd' => $this->fixturePath()]);

            expect($tasks)->toHaveCount(1);
            expect($tasks[0]['patterns'])->toContain('**/*');
            expect($tasks[0]['options']['negative'])->toContain('*.log');
        });
    });

    describe('Gitignore Methods', function (): void {
        test('checks if path is git ignored', function (): void {
            $manager = new GlobbyManager();
            $isIgnored = $manager->isGitIgnored('cake.txt', ['cwd' => $this->fixturePath()]);

            expect($isIgnored)->toBeTrue();
        });

        test('checks if path is not git ignored', function (): void {
            $manager = new GlobbyManager();
            $isIgnored = $manager->isGitIgnored('unicorn.txt', ['cwd' => $this->fixturePath()]);

            expect($isIgnored)->toBeFalse();
        });

        test('checks if path is ignored by custom ignore files with string pattern', function (): void {
            $manager = new GlobbyManager();
            $isIgnored = $manager->isIgnoredByIgnoreFiles('cake.txt', '.gitignore', ['cwd' => $this->fixturePath()]);

            expect($isIgnored)->toBeTrue();
        });

        test('checks if path is ignored by custom ignore files with array patterns', function (): void {
            $manager = new GlobbyManager();
            $isIgnored = $manager->isIgnoredByIgnoreFiles('cake.txt', ['.gitignore'], ['cwd' => $this->fixturePath()]);

            expect($isIgnored)->toBeTrue();
        });

        test('checks if path is not ignored by custom ignore files', function (): void {
            $manager = new GlobbyManager();
            $isIgnored = $manager->isIgnoredByIgnoreFiles('unicorn.txt', '.gitignore', ['cwd' => $this->fixturePath()]);

            expect($isIgnored)->toBeFalse();
        });
    });

    describe('Custom FileSystemAdapter', function (): void {
        test('accepts custom FileSystemAdapter in constructor', function (): void {
            $customFs = new NativeFileSystem();
            $manager = new GlobbyManager($customFs);
            $results = $manager->glob('*.txt', ['cwd' => $this->fixturePath()]);

            expect($results)->toBeArray();
            expect($results)->not->toBeEmpty();
        });

        test('accepts custom FileSystemAdapter via options', function (): void {
            $customFs = new NativeFileSystem();
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'fs' => $customFs,
            ]);

            expect($results)->toBeArray();
            expect($results)->not->toBeEmpty();
        });
    });

    describe('Sad Path', function (): void {
        test('throws exception for non-existent cwd', function (): void {
            $manager = new GlobbyManager();

            expect(fn (): array => $manager->glob('*.txt', ['cwd' => '/non/existent/path']))
                ->toThrow(DirectoryNotFoundException::class);
        });

        test('returns empty array for non-matching pattern', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.xyz', ['cwd' => $this->fixturePath()]);

            expect($results)->toBeArray();
            expect($results)->toBeEmpty();
        });

        test('handles throwErrorOnBrokenSymbolicLink option without error when no broken links', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'throwErrorOnBrokenSymbolicLink' => true,
            ]);

            expect($results)->toBeArray();
            expect($results)->not->toBeEmpty();
        });
    });

    describe('Options', function (): void {
        test('only matches files by default', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*', ['cwd' => $this->fixturePath()]);

            foreach ($results as $path) {
                $fullPath = $this->fixturePath($path);

                if (file_exists($fullPath)) {
                    expect(is_file($fullPath))->toBeTrue();
                }
            }
        });

        test('matches only directories when configured', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*', [
                'cwd' => $this->fixturePath(),
                'onlyDirectories' => true,
            ]);

            foreach ($results as $path) {
                $fullPath = $this->fixturePath($path);

                if (file_exists($fullPath)) {
                    expect(is_dir($fullPath))->toBeTrue();
                }
            }
        });

        test('applies ignore patterns', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'ignore' => ['cake.txt'],
            ]);

            expect($results)->not->toContain('cake.txt');
        });

        test('returns unique results by default', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob(['*.txt', 'unicorn.txt'], ['cwd' => $this->fixturePath()]);

            $uniqueResults = array_unique($results);
            expect($results)->toBe(array_values($uniqueResults));
        });

        test('returns GlobEntry objects when objectMode enabled', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'objectMode' => true,
            ]);

            expect($results)->not->toBeEmpty();
            expect($results[0])->toBeInstanceOf(GlobEntry::class);
            expect($results[0]->path)->toBeString();
            expect($results[0]->name)->toBeString();
        });

        test('includes stats in GlobEntry when stats option enabled', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'objectMode' => true,
                'stats' => true,
            ]);

            expect($results)->not->toBeEmpty();
            expect($results[0]->stats)->not->toBeNull();
        });

        test('marks directories with trailing slash when markDirectories enabled', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*', [
                'cwd' => $this->fixturePath(),
                'onlyDirectories' => true,
                'markDirectories' => true,
            ]);

            foreach ($results as $path) {
                expect($path)->toEndWith(\DIRECTORY_SEPARATOR);
            }
        });

        test('applies ignoreFiles filtering', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'ignoreFiles' => '.gitignore',
            ]);

            expect($results)->not->toContain('cake.txt');
        });

        test('applies ignoreFiles with array of patterns', function (): void {
            $manager = new GlobbyManager();
            $results = $manager->glob('*.txt', [
                'cwd' => $this->fixturePath(),
                'ignoreFiles' => ['.gitignore'],
            ]);

            expect($results)->not->toContain('cake.txt');
        });
    });
});

describe('Globby Facade', function (): void {
    test('provides glob method', function (): void {
        $results = Globby::glob('*.txt', ['cwd' => $this->fixturePath()]);

        expect($results)->toBeArray();
        expect($results)->toContain('unicorn.txt');
    });

    test('provides isDynamicPattern method', function (): void {
        expect(Globby::isDynamicPattern('*.txt'))->toBeTrue();
        expect(Globby::isDynamicPattern('file.txt'))->toBeFalse();
    });

    test('provides convertPathToPattern method', function (): void {
        expect(Globby::convertPathToPattern('file[1].txt'))->toBe('file\\[1\\].txt');
    });

    test('provides stream method', function (): void {
        $generator = Globby::stream('*.txt', ['cwd' => $this->fixturePath()]);

        expect($generator)->toBeInstanceOf(Generator::class);
    });
});
