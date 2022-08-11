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
    function pipeline($passable = [], $pipes = []): Pipeline
    {
        $pipeline = app(Pipeline::class);

        if (filled($passable)) {
            $pipeline->send($passable);
        }

        if (filled($pipes)) {
            $pipeline->through($pipes);
        }

        return $pipeline;
    }
}
