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
    function pipeline($passable, $pipes): Pipeline
    {
        return app(Pipeline::class)
            ->send($passable)
            ->through($pipes);
    }
}
