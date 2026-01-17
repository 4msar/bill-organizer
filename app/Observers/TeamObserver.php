<?php

namespace App\Observers;

use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TeamObserver
{
    /**
     * Handle the Team "created" event.
     */
    public function created(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "updated" event.
     */
    public function updated(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "deleted" event.
     */
    public function deleted(Team $team): void
    {
        DB::transaction(
            function () use ($team) {
                // Delete related bills
                $team->bills()->delete();

                // Delete related transactions
                $team->transactions()->delete();

                // Delete related categories
                $team->categories()->delete();

                // Update related notes
                $team->notes()->update(['team_id' => null]);

                // Detach related users
                $team->users()->detach();
            }
        );
    }

    /**
     * Handle the Team "restored" event.
     */
    public function restored(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "force deleted" event.
     */
    public function forceDeleted(Team $team): void
    {
        //
    }
}
