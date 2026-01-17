<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\DB;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Delete related categories
            $user->categories()->delete();

            // Delete related bills
            $user->bills()->delete();

            // Delete related transactions
            $user->transactions()->delete();

            // Delete related meta and notes
            $user->meta()->delete();

            // Delete related notes
            $user->notes()->delete();

            // Delete related teams
            $user->teams()->delete();

            // Delete related notifications
            $user->notifications()->delete();
        });
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
