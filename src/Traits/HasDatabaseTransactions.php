<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Traits;

use Illuminate\Support\Facades\DB;

trait HasDatabaseTransactions
{
    /**
     * Determines whether class uses transaction.
     */
    protected bool $useTransaction = false;

    /**
     * Enable transaction in pipeline.
     */
    public function withTransaction(): static
    {
        $this->useTransaction = true;

        return $this;
    }

    /**
     * Begin the transaction if enabled.
     */
    protected function beginTransaction(): void
    {
        if (! $this->useTransaction) {
            return;
        }

        DB::beginTransaction();
    }

    /**
     * Commit the transaction if enabled.
     */
    protected function commitTransaction(): void
    {
        if (! $this->useTransaction) {
            return;
        }

        DB::commit();
    }

    /**
     * Rollback the transaction if enabled.
     */
    protected function rollbackTransaction(): void
    {
        if (! $this->useTransaction) {
            return;
        }

        DB::rollBack();
    }
}
