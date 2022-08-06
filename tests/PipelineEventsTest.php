<?php

namespace MichaelRubel\EnhancedPipeline\Tests;

use Illuminate\Support\Facades\Event;
use MichaelRubel\EnhancedPipeline\Events\PipePassed;
use MichaelRubel\EnhancedPipeline\Pipeline;

class PipelineEventsTest extends TestCase
{
    /** @test */
    public function testFiresPipePassedEvents()
    {
        Event::fake();

        app(Pipeline::class)
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
