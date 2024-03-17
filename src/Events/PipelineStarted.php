<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Events;

use Closure;

class PipelineStarted
{
    /**
     * @param  mixed  $passable
     */
    public function __construct(
        public Closure $destination,
        public $passable,
        public array $pipes,
        public bool $useTransaction,
    ) {
        //
    }
}
