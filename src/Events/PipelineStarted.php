<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Events;

use Closure;

class PipelineStarted
{
    public function __construct(
        public Closure $destination,
        public mixed $passable,
        public array $pipes,
        public bool $useTransaction,
    ) {
        //
    }
}
