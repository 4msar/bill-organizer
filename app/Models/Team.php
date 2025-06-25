<?php

namespace App\Models;

use App\Traits\HasMetaData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Team extends Model
{
    const PivotTableName = "team_user";

    protected $fillable = [
        'user_id', // Owner ID
        'name',
        'description',
        'icon',
        'status',
        'currency',
        'currency_symbol',
    ];

    protected $appends = [
        'icon_url',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            // check if the user can access the model via pivot table
            $builder->whereHas('users', function (Builder $builder) {
                // In the users relation
                $builder->where('user_id', Auth::id());
            })->orWhere('user_id', Auth::id());
        });
    }

    function getIconUrlAttribute()
    {
        return Storage::url($this->icon);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, self::PivotTableName);
    }
}
