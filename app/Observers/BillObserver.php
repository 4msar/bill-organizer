<?php

namespace App\Observers;

use App\Enums\WebhookEvent;
use App\Models\Bill;
use App\Services\WebhookService;
use Illuminate\Support\Facades\DB;

final class BillObserver
{
    /**
     * Handle the Bill "created" event.
     */
    public function created(Bill $bill): void
    {
        app(WebhookService::class)->dispatch(
            WebhookEvent::BillingCreated,
            $bill->team_id,
            $bill->toArray()
        );
    }

    /**
     * Handle the Bill "updated" event.
     */
    public function updated(Bill $bill): void
    {
        app(WebhookService::class)->dispatch(
            WebhookEvent::BillingUpdated,
            $bill->team_id,
            $bill->toArray()
        );
    }

    /**
     * Handle the Bill "deleted" event.
     */
    public function deleted(Bill $bill): void
    {
        DB::transaction(function () use ($bill) {
            $bill->transactions()->delete();
            $bill->notes()->detach();
            $bill->meta()->delete();
        });

        app(WebhookService::class)->dispatch(
            WebhookEvent::BillingDeleted,
            $bill->team_id,
            ['id' => $bill->id, 'title' => $bill->title, 'team_id' => $bill->team_id]
        );
    }

    /**
     * Handle the Bill "restored" event.
     */
    public function restored(Bill $bill): void
    {
        //
    }

    /**
     * Handle the Bill "force deleted" event.
     */
    public function forceDeleted(Bill $bill): void
    {
        //
    }
}
