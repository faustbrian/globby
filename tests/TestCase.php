<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use Cline\Globby\GlobbyServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @internal
 *
 * @author Brian Faust <brian@cline.sh>
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Get the package providers.
     *
     * @param  Application              $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            GlobbyServiceProvider::class,
        ];
    }

    /**
     * Get the fixture path.
     *
     * @param  string $path Relative path within fixtures
     * @return string Absolute path to fixture
     */
    protected function fixturePath(string $path = ''): string
    {
        $basePath = __DIR__.'/Fixtures';

        return $path !== '' ? $basePath.'/'.$path : $basePath;
    }
}
