<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Tests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pipeline\Pipeline as OriginalPipeline;
use Illuminate\Support\Facades\DB;
use MichaelRubel\EnhancedPipeline\Pipeline;

class PipelineTest extends TestCase
{
    public function test_exception_is_handled_by_on_failure_method_in_pipeline()
    {
        $result = (new Pipeline)
            ->setContainer(app())
            ->send('data')
            ->through(PipelineWithException::class)
            ->onFailure(function ($piped) {
                return 'error';
            })->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('error', $result);
    }

    public function test_exception_is_handled_by_on_failure_with_piped_data_passed()
    {
        $result = app(\MichaelRubel\EnhancedPipeline\Pipeline::class)
            ->send('data')
            ->through(PipelineWithException::class)
            ->onFailure(function ($piped, $exception) {
                $this->assertInstanceOf(\Exception::class, $exception);

                return $piped;
            })->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('data', $result);
    }

    /** @test */
    public function runs_through_an_entire_pipeline()
    {
        $function1 = function ($piped, $next) {
            $piped = $piped + 1;

            return $next($piped);
        };

        $function2 = function ($piped, $next) {
            $piped = $piped + 2;

            return $next($piped);
        };

        $result = Pipeline::make()
            ->send(0)
            ->through($function1, $function2)
            ->thenReturn();

        $this->assertSame(3, $result);
    }

    /** @test */
    public function throws_exception_from_pipeline()
    {
        $this->expectException(\UnexpectedValueException::class);

        Pipeline::make()
            ->send('test')
            ->through(fn () => throw new \UnexpectedValueException)
            ->thenReturn();
    }

    /** @test */
    public function throws_exception_with_invalid_pipe_type()
    {
        $this->expectException(BindingResolutionException::class);

        Pipeline::make()
            ->send('test')
            ->through('not a callable or class string')
            ->thenReturn();
    }

    /** @test */
    public function accepts_class_strings_as_pipes()
    {
        $result = Pipeline::make()
            ->send('test data')
            ->through(TestPipe::class)
            ->thenReturn();

        $this->assertSame('test data', $result);
    }

    /** @test */
    public function successfully_completes_a_database_transaction()
    {
        $database = DB::spy();

        Pipeline::make()
            ->withTransaction()
            ->send('test')
            ->through(
                fn ($data, $next) => $next($data)
            )->thenReturn();

        $database->shouldHaveReceived('beginTransaction')->once();
        $database->shouldHaveReceived('commit')->once();
    }

    /** @test */
    public function rolls_the_database_transaction_back_on_failure()
    {
        $database = DB::spy();

        rescue(
            fn () => Pipeline::make()
                ->withTransaction()
                ->send('test')
                ->through(fn () => throw new \UnexpectedValueException)
                ->thenReturn(),
        );

        $database->shouldHaveReceived('beginTransaction')->once();
        $database->shouldHaveReceived('rollBack')->once();
    }

    /** @test */
    public function rolls_the_database_transaction_back_on_failure_when_on_failure_method_used()
    {
        $database = DB::spy();

        rescue(
            fn () => Pipeline::make()
                ->withTransaction()
                ->send('test')
                ->through(fn () => throw new \UnexpectedValueException)
                ->onFailure(fn () => true)
                ->thenReturn(),
        );

        $database->shouldHaveReceived('beginTransaction')->once();
        $database->shouldHaveReceived('rollBack')->once();
    }

    /** @test */
    public function test_can_override_original_pipeline()
    {
        $this->app->singleton(OriginalPipeline::class, Pipeline::class);

        $pipeline = app(OriginalPipeline::class);
        $this->assertInstanceOf(Pipeline::class, $pipeline);
    }

    /** @test */
    public function test_can_override_enhanced_pipeline()
    {
        $this->app->singleton(Pipeline::class, OriginalPipeline::class);

        $pipeline = app(Pipeline::class);
        $this->assertInstanceOf(OriginalPipeline::class, $pipeline);
    }
}

class PipelineWithException
{
    public function handle($piped, $next)
    {
        throw new \Exception('Foo');
    }
}

class TestPipe
{
    public function handle($passable)
    {
        return $passable;
    }
}
