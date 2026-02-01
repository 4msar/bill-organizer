<?php

namespace App\Observers;

use App\Models\Bill;
use Illuminate\Support\Facades\DB;

final class BillObserver
{
    /**
     * Handle the Bill "created" event.
     */
    public function created(Bill $bill): void
    {
        //
    }

    /**
     * Handle the Bill "updated" event.
     */
    public function updated(Bill $bill): void
    {
        //
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
