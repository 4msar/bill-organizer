<?php

namespace App\Observers;

use App\Models\Team;
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
            // Delete related meta and notes
            $user->meta()->delete();

            // Delete related notes
            $user->notes()->whereNull('team_id')->delete();

            // Delete related teams
            $user->ownTeams->each->delete();

            // Detach from teams
            $user->teams->each(function (Team $team) use ($user) {
                $team->users()->detach($user->id);
                $team->delete();
            });

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
