<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Tests;

use MichaelRubel\EnhancedPipeline\Pipeline;

class PipelineRunTest extends TestCase
{
    public function test_run_without_params()
    {
        $executed = Pipeline::make()->run(Action::class);

        $this->assertTrue($executed);
    }

    public function test_run_returns_passed_data()
    {
        $data = ['test' => 'yeah'];

        $executed = Pipeline::make()->run(Action::class, with($data));

        $this->assertSame('yeah', $executed['test']);
    }

    public function test_run_has_customizable_method()
    {
        $executed = Pipeline::make()
            ->via('execute')
            ->run(ActionExecute::class);

        $this->assertTrue($executed);
    }

    public function test_run_has_customizable_method_via_container()
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
