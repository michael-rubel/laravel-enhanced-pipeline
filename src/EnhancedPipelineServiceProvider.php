<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline;

use Illuminate\Events\EventServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EnhancedPipelineServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     *
     * @param  Package  $package
     *
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-enhanced-pipeline');
    }

    /**
     * @return void
     */
    public function registeringPackage(): void
    {
        if (! $this->app->bound('events')) {
            $this->app->register(EventServiceProvider::class, true);
        }
    }
}
