<?php

namespace MichaelRubel\EnhancedPipeline\Tests;

use MichaelRubel\EnhancedPipeline\EnhancedPipelineServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            EnhancedPipelineServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('testing');
    }
}
