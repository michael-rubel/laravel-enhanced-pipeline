<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Traits;

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
     * @param  string  $event
     * @param  string|callable|mixed  $pipe
     * @param  mixed  $passable
     *
     * @return void
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
