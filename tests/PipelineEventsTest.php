<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Tests;

use Illuminate\Support\Facades\Event;
use MichaelRubel\EnhancedPipeline\EnhancedPipelineServiceProvider;
use MichaelRubel\EnhancedPipeline\Events\PipeExecutionFinished;
use MichaelRubel\EnhancedPipeline\Events\PipeExecutionStarted;
use MichaelRubel\EnhancedPipeline\Pipeline;

class PipelineEventsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    /** @test */
    public function testMakesSureEventServiceProviderBoots()
    {
        app()->offsetUnset('events');
        $this->assertFalse(app()->bound('events'));

        app()->register(EnhancedPipelineServiceProvider::class, true);
        $this->assertTrue(app()->bound('events'));
    }

    /** @test */
    public function testFiresPipeStartedEvents()
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

    /** @test */
    public function testFiresPipeStartedEventsButFailsToPass()
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

    /** @test */
    public function testFiresPipePassedEvents()
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
            $this->assertInstanceOf(TestPipe::class, app($event->pipe));
            $this->assertSame('data', $event->passable);

            return true;
        }, 2);
    }
}
