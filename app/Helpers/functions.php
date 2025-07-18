<?php

use Illuminate\Database\Eloquent\Model;
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

if (! function_exists('in_fillable')) {
    function in_fillable($name, $modelOrFillables): bool
    {
        if (is_array($modelOrFillables)) {
            return in_array($name, $modelOrFillables);
        }

        if ($modelOrFillables instanceof Model) {
            return in_array($name, $modelOrFillables->getFillable() ?? []);
        }

        $model = new $modelOrFillables();

        return in_array($name, $model->getFillable() ?? []);
    }
}
