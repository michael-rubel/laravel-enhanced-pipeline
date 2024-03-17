<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Tests;

use MichaelRubel\EnhancedPipeline\Pipeline;

class PipelineRunTest extends TestCase
{
    public function testRunWithoutParams()
    {
        $executed = Pipeline::make()->run(Action::class);

        $this->assertTrue($executed);
    }

    public function testRunReturnsPassedData()
    {
        $data = ['test' => 'yeah'];

        $executed = Pipeline::make()->run(Action::class, with($data));

        $this->assertSame('yeah', $executed['test']);
    }

    public function testRunHasCustomizableMethod()
    {
        $executed = Pipeline::make()
            ->via('execute')
            ->run(ActionExecute::class);

        $this->assertTrue($executed);
    }

    public function testRunHasCustomizableMethodViaContainer()
    {
        $this->app->resolving(Pipeline::class, function ($pipeline) {
            return $pipeline->via('execute');
        });

        $executed = Pipeline::make()->run(ActionExecute::class);

        $this->assertTrue($executed);
    }
}

class Action
{
    public function handle(mixed $data, \Closure $next): mixed
    {
        return $next($data);
    }
}

class ActionExecute
{
    public function execute(mixed $data, \Closure $next): mixed
    {
        return $next($data);
    }
}
