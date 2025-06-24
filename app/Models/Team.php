<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Team extends Model
{
    const PivotTableName = "team_user";

    protected $fillable = [
        'user_id', // Owner ID
        'name',
        'description',
        'icon',
        'status',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(function (Builder $builder) {
            // check if the user can access the model via pivot table
            $builder->whereHas('users', function (Builder $builder) {
                $builder->where('user_id', Auth::id());
            })
            ->orWhere('owner_id', Auth::id());
        });
    }

    public function owner() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function users() {
        return $this->belongsToMany(User::class, self::PivotTableName);
    }
}
