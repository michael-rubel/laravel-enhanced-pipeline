<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Traits;

trait HasEvents
{
    /**
     * Determines whether pipeline uses events.
     */
    protected bool $useEvents = false;

    /**
     * Enable events in pipeline.
     */
    public function withEvents(): static
    {
        $this->useEvents = true;

        return $this;
    }

    /**
     * Fire the event if enabled.
     *
     * @param  mixed  ...$params
     */
    protected function fireEvent(string $event, ...$params): void
    {
        if (! $this->useEvents) {
            return;
        }

        event(new $event(...$params));
    }
}
