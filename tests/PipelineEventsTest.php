<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Tests;

use Illuminate\Support\Facades\Event;
use MichaelRubel\EnhancedPipeline\EnhancedPipelineServiceProvider;
use MichaelRubel\EnhancedPipeline\Events\PipePassed;
use MichaelRubel\EnhancedPipeline\Events\PipeStarted;
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

        Event::assertDispatched(function (PipeStarted $event) {
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

        Event::assertDispatched(function (PipeStarted $event) {
            $this->assertInstanceOf(PipelineWithException::class, app($event->pipe));
            $this->assertSame('data', $event->passable);

            return true;
        });

        Event::assertNotDispatched(PipePassed::class);
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

        Event::assertDispatched(function (PipePassed $event) {
            $this->assertInstanceOf(TestPipe::class, app($event->pipe));
            $this->assertSame('data', $event->passable);

            return true;
        }, 2);
    }
}
