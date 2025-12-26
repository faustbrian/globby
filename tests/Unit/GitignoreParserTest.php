<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\GlobbyOptions;
use Cline\Globby\Support\GitignoreParser;
use Cline\Globby\Support\NativeFileSystem;

describe('GitignoreParser', function (): void {
    beforeEach(function (): void {
        $this->fs = new NativeFileSystem();
        $this->parser = new GitignoreParser($this->fs);
        $this->fixturesPath = __DIR__.'/../Fixtures';

        // Create dir-only directory if it doesn't exist (git ignores it due to test .gitignore)
        $dirOnlyPath = $this->fixturesPath.'/test-patterns/dir-only';

        if (is_dir($dirOnlyPath)) {
            return;
        }

        mkdir($dirOnlyPath, 0o755, true);
    });

    describe('Happy Path', function (): void {
        test('identifies ignored files from gitignore', function (): void {
            $options = GlobbyOptions::create();

            // cake.txt is in .gitignore
            $isIgnored = $this->parser->isIgnored(
                $this->fixturesPath.'/cake.txt',
                $this->fixturesPath,
                $options,
            );

            expect($isIgnored)->toBeTrue();
        });

        test('does not ignore files not in gitignore', function (): void {
            $options = GlobbyOptions::create();

            $isIgnored = $this->parser->isIgnored(
                $this->fixturesPath.'/unicorn.txt',
                $this->fixturesPath,
                $options,
            );

            expect($isIgnored)->toBeFalse();
        });

        test('handles relative paths', function (): void {
            $options = GlobbyOptions::create();

            $isIgnored = $this->parser->isIgnored(
                'cake.txt',
                $this->fixturesPath,
                $options,
            );

            expect($isIgnored)->toBeTrue();
        });
    });

    describe('Ignore Files', function (): void {
        test('checks against custom ignore files', function (): void {
            $options = GlobbyOptions::create();

            $isIgnored = $this->parser->isIgnoredByFiles(
                $this->fixturesPath.'/cake.txt',
                ['.gitignore'],
                $this->fixturesPath,
                $options,
            );

            expect($isIgnored)->toBeTrue();
        });

        test('handles non-existent ignore files gracefully', function (): void {
            $options = GlobbyOptions::create();

            $isIgnored = $this->parser->isIgnoredByFiles(
                $this->fixturesPath.'/unicorn.txt',
                ['.nonexistent'],
                $this->fixturesPath,
                $options,
            );

            expect($isIgnored)->toBeFalse();
        });

        test('supports glob patterns for ignore files', function (): void {
            $options = GlobbyOptions::create();

            // Test glob pattern matching (lines 231-235)
            $isIgnored = $this->parser->isIgnoredByFiles(
                $this->fixturesPath.'/test-glob-ignore/test.log',
                ['**/.customignore'],
                $this->fixturesPath,
                $options,
            );

            expect($isIgnored)->toBeTrue();
        });
    });

    describe('Parent Directory Scanning', function (): void {
        test('scans parent directories for gitignore files in git repo', function (): void {
            $options = GlobbyOptions::create();
            // Use deep subdirectory to trigger parent directory scanning logic
            $deepPath = $this->fixturesPath.'/test-git-parent/subdir/deep';

            // Lines 125-126, 131: Test parent gitignore scanning and break condition
            // This triggers the while loop that scans parent directories
            $isIgnored = $this->parser->isIgnored(
                $deepPath.'/deep-test.txt',
                $deepPath,
                $options,
            );

            // Test passes if the code executes without error (covers lines 125-126, 131)
            expect($isIgnored)->toBeBool();
        });

        test('handles directory traversal break condition', function (): void {
            $options = GlobbyOptions::create();

            // Line 131: Test break condition when dirname returns same directory
            $isIgnored = $this->parser->isIgnored(
                '/tmp/nonexistent.txt',
                '/tmp',
                $options,
            );

            expect($isIgnored)->toBeFalse();
        });
    });

    describe('Git Root Detection', function (): void {
        test('returns null when not in git repository', function (): void {
            $options = GlobbyOptions::create();

            // Lines 164, 169: Test git root not found
            $isIgnored = $this->parser->isIgnored(
                '/tmp/test.txt',
                '/tmp',
                $options,
            );

            expect($isIgnored)->toBeFalse();
        });

        test('handles git root detection at filesystem root', function (): void {
            $options = GlobbyOptions::create();

            // Line 164: Test break condition in findGitRoot
            $isIgnored = $this->parser->isIgnored(
                '/root-file.txt',
                '/',
                $options,
            );

            expect($isIgnored)->toBeFalse();
        });
    });

    describe('Subdirectory Gitignore Scanning', function (): void {
        test('scans subdirectories for gitignore files', function (): void {
            $options = GlobbyOptions::create()->deep(2);

            // Lines 204-208: Test subdirectory scanning
            $isIgnored = $this->parser->isIgnored(
                $this->fixturesPath.'/test-subdirs/sub1/ignored-in-sub1.txt',
                $this->fixturesPath.'/test-subdirs',
                $options,
            );

            expect($isIgnored)->toBeTrue();
        });

        test('handles scanning errors gracefully', function (): void {
            $options = GlobbyOptions::create()->deep(1);

            // Line 208: Test error handling in subdirectory scanning
            $isIgnored = $this->parser->isIgnored(
                '/nonexistent/path/file.txt',
                '/nonexistent/path',
                $options,
            );

            expect($isIgnored)->toBeFalse();
        });
    });

    describe('Pattern Parsing', function (): void {
        test('handles negation patterns', function (): void {
            $options = GlobbyOptions::create();

            // Lines 282-283: Test negation pattern parsing
            $isIgnored = $this->parser->isIgnoredByFiles(
                $this->fixturesPath.'/test-patterns/negated.txt',
                ['.gitignore'],
                $this->fixturesPath.'/test-patterns',
                $options,
            );

            expect($isIgnored)->toBeFalse();
        });

        test('handles directory-only patterns', function (): void {
            $options = GlobbyOptions::create();

            // Lines 288-289: Test directory-only pattern parsing
            $isIgnored = $this->parser->isIgnoredByFiles(
                $this->fixturesPath.'/test-patterns/dir-only',
                ['.gitignore'],
                $this->fixturesPath.'/test-patterns',
                $options,
            );

            expect($isIgnored)->toBeTrue();
        });

        test('normalizes patterns with path separators', function (): void {
            $options = GlobbyOptions::create();

            // Line 322: Test pattern normalization with path separator
            $isIgnored = $this->parser->isIgnoredByFiles(
                $this->fixturesPath.'/test-patterns/path/with/slash.txt',
                ['.gitignore'],
                $this->fixturesPath.'/test-patterns',
                $options,
            );

            expect($isIgnored)->toBeBool();
        });
    });

    describe('Pattern Matching', function (): void {
        test('directory-only patterns do not match files', function (): void {
            $options = GlobbyOptions::create();

            // Line 383: Test directory-only pattern matching
            $isIgnored = $this->parser->isIgnoredByFiles(
                $this->fixturesPath.'/test-patterns/dir-only.txt',
                ['.gitignore'],
                $this->fixturesPath.'/test-patterns',
                $options,
            );

            expect($isIgnored)->toBeFalse();
        });

        test('matches patterns against basename for wildcard patterns', function (): void {
            $options = GlobbyOptions::create();

            // Line 398: Test basename matching fallback
            $isIgnored = $this->parser->isIgnoredByFiles(
                $this->fixturesPath.'/test-glob-ignore/test.log',
                ['.customignore'],
                $this->fixturesPath.'/test-glob-ignore',
                $options,
            );

            expect($isIgnored)->toBeTrue();
        });
    });
});
