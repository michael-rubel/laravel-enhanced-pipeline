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
     */
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-enhanced-pipeline');
    }

    /**
     * Register the package.
     */
    public function registeringPackage(): void
    {
        if (! $this->app->bound('events')) {
            $this->app->register(EventServiceProvider::class, true);
        }
    }
}
