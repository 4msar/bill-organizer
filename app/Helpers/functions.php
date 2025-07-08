<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('active_team_id')) {
    /**
     * Get the active team id.
     */
    function active_team_id()
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if (empty($user->active_team_id) && $user->teams->count() > 0) {
            $user->switchTeam($user->teams()->first());
        }

        return $user->active_team_id;
    }
}
