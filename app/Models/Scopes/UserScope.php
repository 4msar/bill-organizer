<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

final class UserScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // if running in console but not in tests, skip the scope
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }
        // User must be authenticated
        // Include both user-specific notes and team notes (where user_id is null)
        $builder->where(function ($query) {
            $query->where('user_id', Auth::id())
                  ->orWhereNull('user_id');
        });
    }
}
