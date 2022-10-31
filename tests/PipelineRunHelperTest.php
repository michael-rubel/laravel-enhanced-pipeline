<?php

namespace MichaelRubel\EnhancedPipeline\Tests;

use MichaelRubel\EnhancedPipeline\Pipeline;

class PipelineRunHelperTest extends TestCase
{
    public function testRunHelperWithoutParams()
    {
        $executed = run(Action::class);

        $this->assertTrue($executed);
    }

    public function testRunHelperActionReturnsPassedData()
    {
        $data = ['test' => 'yeah'];

        $executed = run(Action::class, with($data));

        $this->assertSame('yeah', $executed['test']);
    }

    public function testRunHelperHasCustomizableMethod()
    {
        $this->app->resolving(Pipeline::class, function ($pipeline) {
            return $pipeline->via('execute');
        });

        $executed = run(ActionExecute::class);

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
