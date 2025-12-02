<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\GlobbyOptions;
use Cline\Globby\Support\NativeFileSystem;
use Cline\Globby\Support\PatternMatcher;

describe('PatternMatcher', function (): void {
    beforeEach(function (): void {
        $this->fs = new NativeFileSystem();
        $this->matcher = new PatternMatcher($this->fs);
    });

    describe('Pattern Detection', function (): void {
        test('identifies asterisk as dynamic', function (): void {
            expect($this->matcher->isDynamic('*.txt'))->toBeTrue();
            expect($this->matcher->isDynamic('file*'))->toBeTrue();
            expect($this->matcher->isDynamic('*'))->toBeTrue();
        });

        test('identifies globstar as dynamic', function (): void {
            expect($this->matcher->isDynamic('**/*.txt'))->toBeTrue();
            expect($this->matcher->isDynamic('src/**'))->toBeTrue();
        });

        test('identifies question mark as dynamic', function (): void {
            expect($this->matcher->isDynamic('file?.txt'))->toBeTrue();
            expect($this->matcher->isDynamic('???'))->toBeTrue();
        });

        test('identifies brackets as dynamic', function (): void {
            expect($this->matcher->isDynamic('[abc].txt'))->toBeTrue();
            expect($this->matcher->isDynamic('file[0-9].txt'))->toBeTrue();
        });

        test('identifies braces as dynamic', function (): void {
            expect($this->matcher->isDynamic('{foo,bar}.txt'))->toBeTrue();
            expect($this->matcher->isDynamic('file.{js,ts}'))->toBeTrue();
        });

        test('identifies static patterns', function (): void {
            expect($this->matcher->isDynamic('file.txt'))->toBeFalse();
            expect($this->matcher->isDynamic('path/to/file.txt'))->toBeFalse();
            expect($this->matcher->isDynamic('exact-name'))->toBeFalse();
        });
    });

    describe('Pattern Escaping', function (): void {
        test('escapes asterisks', function (): void {
            expect($this->matcher->escapePattern('file*.txt'))->toBe('file\\*.txt');
        });

        test('escapes question marks', function (): void {
            expect($this->matcher->escapePattern('file?.txt'))->toBe('file\\?.txt');
        });

        test('escapes square brackets', function (): void {
            expect($this->matcher->escapePattern('file[1].txt'))->toBe('file\\[1\\].txt');
        });

        test('escapes curly braces', function (): void {
            expect($this->matcher->escapePattern('{a,b}.txt'))->toBe('\\{a,b\\}.txt');
        });

        test('escapes parentheses', function (): void {
            expect($this->matcher->escapePattern('(test).txt'))->toBe('\\(test\\).txt');
        });

        test('converts backslashes to forward slashes', function (): void {
            $result = $this->matcher->escapePattern('path\\to\\file.txt');
            expect(str_contains($result, '\\\\'))->toBeFalse();
        });

        test('handles multiple special characters', function (): void {
            $result = $this->matcher->escapePattern('file[1]*(test).txt');
            expect($result)->toBe('file\\[1\\]\\*\\(test\\).txt');
        });
    });

    describe('Pattern Matching', function (): void {
        test('matches exact filename pattern', function (): void {
            expect($this->matcher->matchesPattern('file.txt', 'file.txt', '/tmp'))->toBeTrue();
            expect($this->matcher->matchesPattern('file.txt', 'other.txt', '/tmp'))->toBeFalse();
        });

        test('matches wildcard patterns', function (): void {
            expect($this->matcher->matchesPattern('file.txt', '*.txt', '/tmp'))->toBeTrue();
            expect($this->matcher->matchesPattern('file.txt', 'file.*', '/tmp'))->toBeTrue();
            expect($this->matcher->matchesPattern('file.txt', '*.js', '/tmp'))->toBeFalse();
        });

        test('matches single character wildcard', function (): void {
            expect($this->matcher->matchesPattern('file1.txt', 'file?.txt', '/tmp'))->toBeTrue();
            expect($this->matcher->matchesPattern('file.txt', 'file?.txt', '/tmp'))->toBeFalse();
        });

        test('matches globstar patterns', function (): void {
            expect($this->matcher->matchesPattern('src/file.txt', '**/file.txt', '/tmp'))->toBeTrue();
            expect($this->matcher->matchesPattern('deep/nested/file.txt', '**/file.txt', '/tmp'))->toBeTrue();
        });

        test('matches path patterns', function (): void {
            expect($this->matcher->matchesPattern('src/file.txt', 'src/*.txt', '/tmp'))->toBeTrue();
            expect($this->matcher->matchesPattern('lib/file.txt', 'src/*.txt', '/tmp'))->toBeFalse();
        });
    });

    describe('Absolute Patterns', function (): void {
        test('matches absolute path patterns with onlyFiles true', function (): void {
            $options = GlobbyOptions::create()->onlyFiles(true);
            $pattern = $this->fixturePath().'/*.txt';

            $results = $this->matcher->match($pattern, $this->fixturePath(), $options);

            expect($results)->toBeArray();
            expect($results)->not->toBeEmpty();
        });

        test('matches absolute path patterns with onlyFiles false', function (): void {
            $options = GlobbyOptions::create()->onlyFiles(false);
            $pattern = $this->fixturePath().'/*';

            $results = $this->matcher->match($pattern, $this->fixturePath(), $options);

            expect($results)->toBeArray();
        });
    });

    describe('Recursive Patterns', function (): void {
        test('handles non-existent base directory', function (): void {
            $options = GlobbyOptions::create();

            $results = $this->matcher->match('nonexistent/**/*.txt', $this->fixturePath(), $options);

            expect($results)->toBe([]);
        });

        test('respects depth limit when set', function (): void {
            $options = GlobbyOptions::create()->deep(1);

            $results = $this->matcher->match('**/*.txt', $this->fixturePath(), $options);

            expect($results)->toBeArray();
        });

        test('matches recursive patterns without depth limit', function (): void {
            $options = GlobbyOptions::create();

            $results = $this->matcher->match('**/*.txt', $this->fixturePath(), $options);

            expect($results)->toBeArray();
            expect($results)->not->toBeEmpty();
        });

        test('matches recursive patterns with dotfiles when dot option is enabled', function (): void {
            $options = GlobbyOptions::create()->dot(true);

            $results = $this->matcher->match('**/*', $this->fixturePath(), $options);

            expect($results)->toBeArray();
            // Should include hidden files
            $hasHidden = false;

            foreach ($results as $path) {
                if (str_contains(basename($path), '.git') || basename($path) === '.hidden') {
                    $hasHidden = true;

                    break;
                }
            }

            expect($hasHidden)->toBeTrue();
        });

        test('skips dotfiles when dot option is disabled', function (): void {
            $options = GlobbyOptions::create()->dot(false);

            $results = $this->matcher->match('**/*.txt', $this->fixturePath(), $options);

            expect($results)->toBeArray();

            // Should not include .gitignore files
            foreach ($results as $path) {
                $basename = basename($path);
                expect(str_starts_with($basename, '.'))->toBeFalse();
            }
        });
    });

    describe('Error Handling', function (): void {
        test('suppresses errors when suppressErrors is true', function (): void {
            $options = GlobbyOptions::create()->suppressErrors(true);

            $results = $this->matcher->match('**/*.txt', $this->fixturePath(), $options);

            expect($results)->toBeArray();
        });

        test('does not throw exception when suppressErrors is false in normal operation', function (): void {
            $options = GlobbyOptions::create()->suppressErrors(false);

            // Normal operation should not throw
            $results = $this->matcher->match('**/*.txt', $this->fixturePath(), $options);

            expect($results)->toBeArray();
        });
    });
});
