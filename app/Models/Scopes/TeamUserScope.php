<?php

namespace App\Models\Scopes;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

final class TeamUserScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // if running in console but not in tests, skip the scope
        if (app()->runningInConsole()) {
            return;
        }

        $table = Team::TableName;
        // check if the user can access the model via pivot table
        $builder->whereHas('users', function (Builder $query) {
            // In the users relation
            $query->where('user_id', Auth::id());
        })->orWhere("{$table}.user_id", Auth::id());
    }
}
