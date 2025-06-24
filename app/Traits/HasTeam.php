<?php

namespace App\Traits;

use App\Models\Team;

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
        static::creating(function ($model) {
            if(empty($model->team_id)){
                $model->team_id = active_team_id();
            }
        });
    }
}
