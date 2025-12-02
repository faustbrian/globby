<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Facades;

use Cline\Globby\GlobbyManager;
use Cline\Globby\GlobEntry;
use Generator;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for user-friendly glob matching operations.
 *
 * Provides a static interface to the Globby glob matching functionality
 * with support for multiple patterns, negation, directory expansion,
 * and gitignore integration. Acts as a convenient entry point for all
 * glob operations without requiring direct manager instantiation.
 *
 * @method static bool                                                                 isDynamicPattern(string $pattern) Check if a pattern contains dynamic glob characters (* ? [ ] { })
 * @method static string                                                               convertPathToPattern(string $path)                                                                                      Convert a file path to an escaped glob pattern by escaping special characters
 * @method static array<array{patterns: array<string>, options: array<string, mixed>}> generateGlobTasks(array<int, string>|string $patterns, array<string, mixed>|null $options = null)                       Generate glob tasks compatible with external glob libraries
 * @method static array<GlobEntry|string>                                              glob(array<int, string>|string $patterns, array<string, mixed>|null $options = null)                                    Find all files matching the given glob patterns
 * @method static bool                                                                 isGitIgnored(string $path, array<string, mixed>|null $options = null)                                                   Check if a path is ignored by .gitignore rules
 * @method static bool                                                                 isIgnoredByIgnoreFiles(string $path, array<int, string>|string $ignoreFiles, array<string, mixed>|null $options = null) Check if a path is ignored by specified ignore files
 * @method static Generator<int, GlobEntry|string, mixed, void>                        stream(array<int, string>|string $patterns, array<string, mixed>|null $options = null)                                  Stream files matching the given glob patterns as a generator
 *
 * @see GlobbyManager
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Globby extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string The service container binding key
     */
    protected static function getFacadeAccessor(): string
    {
        return GlobbyManager::class;
    }
}
