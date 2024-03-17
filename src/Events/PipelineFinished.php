<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Events;

class PipelineFinished
{
    public function __construct(
        public mixed $destination,
        public mixed $passable,
        public array $pipes,
        public bool $useTransaction,
        public mixed $result,
    ) {
        //
    }
}
