<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Events;

class PipeExecutionFinished
{
    public function __construct(
        public mixed $pipe,
        public mixed $passable,
    ) {
        //
    }
}
