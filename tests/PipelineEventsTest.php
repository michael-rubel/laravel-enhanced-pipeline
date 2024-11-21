<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Tests;

use Closure;
use Illuminate\Support\Facades\Event;
use MichaelRubel\EnhancedPipeline\EnhancedPipelineServiceProvider;
use MichaelRubel\EnhancedPipeline\Events\PipeExecutionFinished;
use MichaelRubel\EnhancedPipeline\Events\PipeExecutionStarted;
use MichaelRubel\EnhancedPipeline\Events\PipelineFinished;
use MichaelRubel\EnhancedPipeline\Events\PipelineStarted;
use MichaelRubel\EnhancedPipeline\Pipeline;

class PipelineEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    public function test_makes_sure_event_service_provider_boots()
    {
        app()->offsetUnset('events');
        $this->assertFalse(app()->bound('events'));

        app()->register(EnhancedPipelineServiceProvider::class, true);
        $this->assertTrue(app()->bound('events'));
    }

    public function test_fires_pipeline_started_event()
    {
        app(Pipeline::class)
            ->withEvents()
            ->send('data')
            ->thenReturn();

        Event::assertDispatched(function (PipelineStarted $event) {
            $this->assertInstanceOf(Closure::class, $event->destination);
            $this->assertSame('data', $event->passable);
            $this->assertSame([], $event->pipes);
            $this->assertFalse($event->useTransaction);

            return true;
        });
    }

    public function test_fires_pipeline_finished_event()
    {
        app(Pipeline::class)
            ->withEvents()
            ->send('data')
            ->thenReturn();

        Event::assertDispatched(function (PipelineFinished $event) {
            $this->assertInstanceOf(Closure::class, $event->destination);
            $this->assertSame('data', $event->passable);
            $this->assertSame([], $event->pipes);
            $this->assertFalse($event->useTransaction);
            $this->assertSame('data', $event->result);

            return true;
        });
    }

    public function test_fires_pipe_execution_started_event()
    {
        app(Pipeline::class)
            ->withEvents()
            ->send('data')
            ->through([
                TestPipe::class,
                TestPipe::class,
            ])
            ->thenReturn();

        Event::assertDispatched(function (PipeExecutionStarted $event) {
            $this->assertInstanceOf(TestPipe::class, app($event->pipe));
            $this->assertSame('data', $event->passable);

            return true;
        }, 2);
    }

    public function test_fires_pipe_execution_started_event_but_fails_to_finish()
    {
        app(Pipeline::class)
            ->withEvents()
            ->send('data')
            ->through(PipelineWithException::class)
            ->onFailure(fn () => true)
            ->thenReturn();

        Event::assertDispatched(function (PipeExecutionStarted $event) {
            $this->assertInstanceOf(PipelineWithException::class, app($event->pipe));
            $this->assertSame('data', $event->passable);

            return true;
        });

        Event::assertNotDispatched(PipeExecutionFinished::class);
    }

    public function test_fires_pipe_execution_finished_event()
    {
        app(Pipeline::class)
            ->withEvents()
            ->send('data')
            ->through([
                TestPipe::class,
                TestPipe::class,
            ])
            ->thenReturn();

        Event::assertDispatched(function (PipeExecutionFinished $event) {
            $this->assertInstanceOf(TestPipe::class, $event->pipe);
            $this->assertSame('data', $event->passable);

            return true;
        }, 2);
    }
}
