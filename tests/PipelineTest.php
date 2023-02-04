<?php

namespace MichaelRubel\EnhancedPipeline\Tests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pipeline\Pipeline as OriginalPipeline;
use Illuminate\Support\Facades\DB;
use MichaelRubel\EnhancedPipeline\Pipeline;

class PipelineTest extends TestCase
{
    public function testExceptionIsHandledByOnFailureMethodInPipeline()
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

    public function testExceptionIsHandledByOnFailureWithPipedDataPassed()
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
    public function runsThroughAnEntirePipeline()
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
    public function throwsExceptionFromPipeline()
    {
        $this->expectException(\UnexpectedValueException::class);

        Pipeline::make()
            ->send('test')
            ->through(fn () => throw new \UnexpectedValueException)
            ->thenReturn();
    }

    /** @test */
    public function throwsExceptionWithInvalidPipeType()
    {
        $this->expectException(BindingResolutionException::class);

        Pipeline::make()
            ->send('test')
            ->through('not a callable or class string')
            ->thenReturn();
    }

    /** @test */
    public function acceptsClassStringsAsPipes()
    {
        $result = Pipeline::make()
            ->send('test data')
            ->through(TestPipe::class)
            ->thenReturn();

        $this->assertSame('test data', $result);
    }

    /** @test */
    public function successfullyCompletesADatabaseTransaction()
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
    public function rollsTheDatabaseTransactionBackOnFailure()
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
    public function rollsTheDatabaseTransactionBackOnFailureWhenOnFailureMethodUsed()
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
    public function testPipelineHelper()
    {
        $test = pipeline('test', fn ($data, $next) => $next($data))
            ->thenReturn();

        $this->assertSame('test', $test);
    }

    /** @test */
    public function testPipelineHelperWithoutParameters()
    {
        $test = pipeline()
            ->send('data')
            ->through(TestPipe::class)
            ->thenReturn();

        $this->assertSame('data', $test);
    }

    /** @test */
    public function testCanOverrideOriginalPipeline()
    {
        $this->app->singleton(OriginalPipeline::class, Pipeline::class);

        $pipeline = app(OriginalPipeline::class);
        $this->assertInstanceOf(Pipeline::class, $pipeline);
    }

    /** @test */
    public function testCanOverrideEnhancedPipeline()
    {
        $this->app->singleton(Pipeline::class, OriginalPipeline::class);

        $pipeline = app(Pipeline::class);
        $this->assertInstanceOf(OriginalPipeline::class, $pipeline);

        $pipeline = pipeline();
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
