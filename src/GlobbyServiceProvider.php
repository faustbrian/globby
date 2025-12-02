<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\Globby;

use Override;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Service provider for the Globby package.
 *
 * Registers the GlobbyManager singleton and publishes configuration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class GlobbyServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package settings.
     *
     * Defines package configuration including the package name and config file.
     *
     * @param Package $package The package configuration instance to configure
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('globby')
            ->hasConfigFile();
    }

    /**
     * Register the package's services in the container.
     *
     * Binds the GlobbyManager as a singleton.
     */
    #[Override()]
    public function registeringPackage(): void
    {
        $this->app->singleton(fn (): GlobbyManager => new GlobbyManager());
    }
}
