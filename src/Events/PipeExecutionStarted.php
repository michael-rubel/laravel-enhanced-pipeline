<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Events;

class PipeExecutionStarted
{
    public function __construct(
        public mixed $pipe,
        public mixed $passable,
    ) {
        //
    }
}
