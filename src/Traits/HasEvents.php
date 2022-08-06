<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Traits;

use MichaelRubel\EnhancedPipeline\Events\PipePassed;
use MichaelRubel\EnhancedPipeline\Events\PipeStarted;

trait HasEvents
{
    /**
     * Determines whether pipeline uses events.
     *
     * @var bool
     */
    protected bool $useEvents = false;

    /**
     * Enable events in pipeline.
     *
     * @return static
     */
    public function withEvents(): static
    {
        $this->useEvents = true;

        return $this;
    }

    /**
     * Fire the started event if enabled.
     *
     * @param  mixed  $pipe
     * @param  mixed  $passable
     *
     * @return void
     */
    protected function fireStartedEvent($pipe, $passable): void
    {
        if (! $this->useEvents) {
            return;
        }

        event(new PipeStarted($pipe, $passable));
    }

    /**
     * Fire the passed event if enabled.
     *
     * @param  mixed  $pipe
     * @param  mixed  $passable
     *
     * @return void
     */
    protected function firePassedEvent($pipe, $passable): void
    {
        if (! $this->useEvents) {
            return;
        }

        event(new PipePassed($pipe, $passable));
    }
}
