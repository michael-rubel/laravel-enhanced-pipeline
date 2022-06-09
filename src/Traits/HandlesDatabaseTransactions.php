<?php

declare(strict_types=1);

namespace MichaelRubel\EnhancedPipeline\Traits;

use Illuminate\Support\Facades\DB;

trait HandlesDatabaseTransactions
{
    /**
     * Determines whether class uses transaction.
     *
     * @var bool
     */
    protected bool $useTransaction = false;

    /**
     * Begin the transaction if enabled.
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    protected function rollbackTransaction(): void
    {
        if (! $this->useTransaction) {
            return;
        }

        DB::rollBack();
    }
}
