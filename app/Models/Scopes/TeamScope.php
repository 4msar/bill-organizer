<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TeamScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // if running in console but not in tests, skip the scope
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        $builder->where('team_id', active_team_id());
    }
}
