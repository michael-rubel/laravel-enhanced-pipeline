<?php

declare(strict_types=1);

use MichaelRubel\EnhancedPipeline\Pipeline;

if (! function_exists('pipeline')) {
    /**
     * @param  mixed  $passable
     * @param  array|mixed  $pipes
     *
     * @return Pipeline
     */
    function pipeline($passable = null, $pipes = null): Pipeline
    {
        $pipeline = app(Pipeline::class);

        if (! is_null($passable)) {
            $pipeline->send($passable);
        }

        if (! is_null($pipes)) {
            $pipeline->through($pipes);
        }

        return $pipeline;
    }
}
