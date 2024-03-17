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
     * @param  string|callable|mixed  $pipe
     * @param  mixed  $passable
     */
    protected function fireEvent(string $event, $pipe, $passable): void
    {
        if (! $this->useEvents) {
            return;
        }

        if (is_object($pipe)) {
            /** @var object $pipe */
            $pipe = $pipe::class;
        }

        event(new $event($pipe, $passable));
    }
}
