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
 * Registers the GlobbyManager singleton in Laravel's service container
 * and publishes package configuration. Extends Spatie's PackageServiceProvider
 * for streamlined package registration.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class GlobbyServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package settings.
     *
     * Defines package metadata including the package identifier and configuration
     * file publishing. Called automatically during service provider registration.
     *
     * @param Package $package The package configuration instance provided by Spatie's PackageServiceProvider
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
     * Binds the GlobbyManager as a singleton to ensure a single shared instance
     * throughout the application lifecycle. The manager is instantiated with
     * default file system adapter on first access.
     */
    #[Override()]
    public function registeringPackage(): void
    {
        $this->app->singleton(fn (): GlobbyManager => new GlobbyManager());
    }
}
