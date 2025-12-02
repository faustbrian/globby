<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\Globby\GlobbyOptions;
use Cline\Globby\Support\NativeFileSystem;

describe('GlobbyOptions', function (): void {
    describe('Happy Path', function (): void {
        test('creates with default values', function (): void {
            $options = GlobbyOptions::create();

            expect($options->getCwd())->toBeNull();
            expect($options->getExpandDirectories())->toBeTrue();
            expect($options->getGitignore())->toBeFalse();
            expect($options->getOnlyFiles())->toBeTrue();
            expect($options->getDot())->toBeFalse();
            expect($options->getAbsolute())->toBeFalse();
            expect($options->getUnique())->toBeTrue();
        });

        test('allows fluent configuration', function (): void {
            $options = GlobbyOptions::create()
                ->cwd('/tmp')
                ->gitignore(true)
                ->dot(true)
                ->absolute(true);

            expect($options->getCwd())->toBe('/tmp');
            expect($options->getGitignore())->toBeTrue();
            expect($options->getDot())->toBeTrue();
            expect($options->getAbsolute())->toBeTrue();
        });

        test('sets expand directories with boolean', function (): void {
            $options = GlobbyOptions::create()->expandDirectories(false);

            expect($options->getExpandDirectories())->toBeFalse();
        });

        test('sets expand directories with array config', function (): void {
            $config = [
                'files' => ['*.php'],
                'extensions' => ['php', 'js'],
            ];
            $options = GlobbyOptions::create()->expandDirectories($config);

            expect($options->getExpandDirectories())->toBe($config);
        });

        test('sets ignore files as string', function (): void {
            $options = GlobbyOptions::create()->ignoreFiles('.gitignore');

            expect($options->getIgnoreFiles())->toBe('.gitignore');
        });

        test('sets ignore files as array', function (): void {
            $options = GlobbyOptions::create()->ignoreFiles(['.gitignore', '.npmignore']);

            expect($options->getIgnoreFiles())->toBe(['.gitignore', '.npmignore']);
        });

        test('sets ignore patterns', function (): void {
            $patterns = ['node_modules/**', 'vendor/**'];
            $options = GlobbyOptions::create()->ignore($patterns);

            expect($options->getIgnore())->toBe($patterns);
        });

        test('sets deep limit', function (): void {
            $options = GlobbyOptions::create()->deep(5);

            expect($options->getDeep())->toBe(5);
        });

        test('sets follow symbolic links', function (): void {
            $options = GlobbyOptions::create()->followSymbolicLinks(false);

            expect($options->getFollowSymbolicLinks())->toBeFalse();
        });

        test('sets suppress errors', function (): void {
            $options = GlobbyOptions::create()->suppressErrors(true);

            expect($options->getSuppressErrors())->toBeTrue();
        });

        test('accepts custom file system adapter', function (): void {
            $fs = new NativeFileSystem();
            $options = GlobbyOptions::create()->fs($fs);

            expect($options->getFs())->toBe($fs);
        });

        test('onlyFiles and onlyDirectories are mutually exclusive', function (): void {
            $options = GlobbyOptions::create()
                ->onlyFiles(true)
                ->onlyDirectories(true);

            expect($options->getOnlyDirectories())->toBeTrue();
            expect($options->getOnlyFiles())->toBeFalse();

            $options->onlyFiles(true);
            expect($options->getOnlyFiles())->toBeTrue();
            expect($options->getOnlyDirectories())->toBeFalse();
        });

        test('sets unique', function (): void {
            $options = GlobbyOptions::create()->unique(false);

            expect($options->getUnique())->toBeFalse();
        });

        test('sets mark directories', function (): void {
            $options = GlobbyOptions::create()->markDirectories(true);

            expect($options->getMarkDirectories())->toBeTrue();
        });

        test('sets case sensitive match', function (): void {
            $options = GlobbyOptions::create()->caseSensitiveMatch(false);

            expect($options->getCaseSensitiveMatch())->toBeFalse();
        });

        test('sets base name match', function (): void {
            $options = GlobbyOptions::create()->baseNameMatch(true);

            expect($options->getBaseNameMatch())->toBeTrue();
        });

        test('sets throw error on broken symbolic link', function (): void {
            $options = GlobbyOptions::create()->throwErrorOnBrokenSymbolicLink(true);

            expect($options->getThrowErrorOnBrokenSymbolicLink())->toBeTrue();
        });

        test('sets object mode', function (): void {
            $options = GlobbyOptions::create()->objectMode(true);

            expect($options->getObjectMode())->toBeTrue();
        });

        test('sets stats and enables object mode', function (): void {
            $options = GlobbyOptions::create()->stats(true);

            expect($options->getStats())->toBeTrue();
            expect($options->getObjectMode())->toBeTrue();
        });

        test('getObjectMode returns true when stats is true', function (): void {
            $options = GlobbyOptions::create()
                ->objectMode(false)
                ->stats(true);

            expect($options->getObjectMode())->toBeTrue();
        });
    });

    describe('Array Conversion', function (): void {
        test('converts to array', function (): void {
            $options = GlobbyOptions::create()
                ->cwd('/tmp')
                ->gitignore(true)
                ->dot(true);

            $array = $options->toArray();

            expect($array)->toBeArray();
            expect($array['cwd'])->toBe('/tmp');
            expect($array['gitignore'])->toBeTrue();
            expect($array['dot'])->toBeTrue();
        });

        test('creates from array', function (): void {
            $array = [
                'cwd' => '/tmp',
                'gitignore' => true,
                'dot' => true,
                'deep' => 10,
                'ignore' => ['*.log'],
            ];

            $options = GlobbyOptions::fromArray($array);

            expect($options->getCwd())->toBe('/tmp');
            expect($options->getGitignore())->toBeTrue();
            expect($options->getDot())->toBeTrue();
            expect($options->getDeep())->toBe(10);
            expect($options->getIgnore())->toBe(['*.log']);
        });

        test('creates from empty array with defaults', function (): void {
            $options = GlobbyOptions::fromArray([]);

            expect($options->getCwd())->toBeNull();
            expect($options->getExpandDirectories())->toBeTrue();
            expect($options->getGitignore())->toBeFalse();
        });

        test('ignores invalid option types', function (): void {
            $options = GlobbyOptions::fromArray([
                'cwd' => 123, // Invalid: should be string
                'gitignore' => 'yes', // Invalid: should be bool
            ]);

            expect($options->getCwd())->toBeNull();
            expect($options->getGitignore())->toBeFalse();
        });

        test('creates from array with all boolean options', function (): void {
            $array = [
                'cwd' => '/tmp',
                'followSymbolicLinks' => false,
                'suppressErrors' => true,
                'absolute' => true,
                'unique' => false,
                'markDirectories' => true,
                'caseSensitiveMatch' => false,
                'baseNameMatch' => true,
                'throwErrorOnBrokenSymbolicLink' => true,
                'objectMode' => true,
                'stats' => true,
            ];

            $options = GlobbyOptions::fromArray($array);

            expect($options->getFollowSymbolicLinks())->toBeFalse();
            expect($options->getSuppressErrors())->toBeTrue();
            expect($options->getAbsolute())->toBeTrue();
            expect($options->getUnique())->toBeFalse();
            expect($options->getMarkDirectories())->toBeTrue();
            expect($options->getCaseSensitiveMatch())->toBeFalse();
            expect($options->getBaseNameMatch())->toBeTrue();
            expect($options->getThrowErrorOnBrokenSymbolicLink())->toBeTrue();
            expect($options->getObjectMode())->toBeTrue();
            expect($options->getStats())->toBeTrue();
        });

        test('creates from array with ignoreFiles', function (): void {
            $options = GlobbyOptions::fromArray([
                'ignoreFiles' => ['.gitignore', '.npmignore'],
            ]);

            expect($options->getIgnoreFiles())->toBe(['.gitignore', '.npmignore']);
        });
    });
});
