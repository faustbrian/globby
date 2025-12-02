<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby\Facades;

use Cline\Globby\GlobbyManager;
use Generator;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for user-friendly glob matching operations.
 *
 * Provides a static interface to the Globby glob matching functionality
 * with support for multiple patterns, negation, directory expansion,
 * and gitignore integration.
 *
 * @method static string                                                               convertPathToPattern(string $path)
 * @method static array<array{patterns: array<string>, options: array<string, mixed>}> generateGlobTasks(array<int, string>|string $patterns, array<string, mixed>|null $options = null)
 * @method static array<string>                                                        glob(array<int, string>|string $patterns, array<string, mixed>|null $options = null)
 * @method static bool                                                                 isDynamicPattern(string $pattern)
 * @method static bool                                                                 isGitIgnored(string $path, array<string, mixed>|null $options = null)
 * @method static bool                                                                 isIgnoredByIgnoreFiles(string $path, array<int, string>|string $ignoreFiles, array<string, mixed>|null $options = null)
 * @method static Generator<int, string, mixed, void>                                  stream(array<int, string>|string $patterns, array<string, mixed>|null $options = null)
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
