<?php

declare(strict_types=1);

use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
use MichaelRubel\EnhancedPipeline\Pipeline;

if (! function_exists('pipeline')) {
    /**
     * @param  mixed  $passable
     * @param  array|mixed  $pipes
     *
     * @return Pipeline
     */
    function pipeline($passable = null, $pipes = null): PipelineContract
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

if (! function_exists('run')) {
    /**
     * @param  string  $action
     * @param  mixed  $data
     * @return mixed
     */
    function run(string $action, mixed $data = true): mixed
    {
        return app(Pipeline::class)
            ->send($data)
            ->through([$action])
            ->thenReturn();
    }
}
