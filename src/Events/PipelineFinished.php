<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Events;

use Closure;

class PipelineFinished
{
    public function __construct(
        public Closure $destination,
        public mixed $passable,
        public array $pipes,
        public bool $useTransaction,
        public mixed $result,
    ) {
        //
    }
}
