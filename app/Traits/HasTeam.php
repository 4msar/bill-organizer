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
        return $this->belongsTo(Team::class);
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

            if (in_array('user_id', $model->getFillable())) {
                $model->user_id = Auth::id();
            }
        });
    }
}
