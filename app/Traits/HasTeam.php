<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HasTeam
{
    /**
     * Get the team
     */
    public function team()
    {
        return $this->belongsTo(Team::class)->withoutGlobalScope('user');
    }

    /**
     * Boot the trait
     */
    public static function bootHasTeam()
    {
        static::creating(function (Model $model) {
            if (empty($model->team_id)) {
                $model->team_id = active_team_id();
            }

            // Only set user_id if it's not explicitly set and the model doesn't already have a user_id attribute set
            if (
                in_array('user_id', $model->getFillable()) &&
                ! array_key_exists('user_id', $model->getAttributes())
            ) {
                $model->user_id = Auth::id();
            }
        });
    }
}
