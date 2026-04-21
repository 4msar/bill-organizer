<?php

namespace App\Observers;

use App\Enums\WebhookEvent;
use App\Models\Transaction;
use App\Services\WebhookService;

final class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        app(WebhookService::class)->dispatch(
            WebhookEvent::TransactionCreated,
            $transaction->team_id,
            $transaction->toArray()
        );
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        app(WebhookService::class)->dispatch(
            WebhookEvent::TransactionDeleted,
            $transaction->team_id,
            ['id' => $transaction->id, 'bill_id' => $transaction->bill_id, 'team_id' => $transaction->team_id]
        );
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
