<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tests\TestCase;

pest()->extend(TestCase::class)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Dynamic Test Fixtures
|--------------------------------------------------------------------------
|
| These fixtures are created dynamically to avoid git-ignored files being
| missing in CI. Files listed in .gitignore (for testing gitignore parsing)
| must exist on disk but can't be committed normally.
|
*/

uses()->beforeAll(function (): void {
    $fixtures = __DIR__.'/Fixtures';

    // Create cake.txt (listed in .gitignore for gitignore parsing tests)
    touch($fixtures.'/cake.txt');

    // Create data*.log files (may be ignored by global gitignore)
    touch($fixtures.'/complex-patterns/data0.log');
    touch($fixtures.'/complex-patterns/data5.log');
    touch($fixtures.'/complex-patterns/data9.log');
})->in(__DIR__);
