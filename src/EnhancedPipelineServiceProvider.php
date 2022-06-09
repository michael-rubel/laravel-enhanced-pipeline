<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EnhancedPipelineServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     *
     * @param Package $package
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
    public function packageRegistered(): void
    {
        $this->app->scoped(
            \Illuminate\Pipeline\Pipeline::class,
            \MichaelRubel\EnhancedPipeline\Pipeline::class
        );
    }
}
