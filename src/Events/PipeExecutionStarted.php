<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Events;

class PipeExecutionStarted
{
    /**
     * @param  mixed  $pipe
     * @param  mixed  $passable
     */
    public function __construct(public $pipe, public $passable)
    {
        //
    }
}
