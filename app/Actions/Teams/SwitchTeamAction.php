<?php

namespace App\Actions\Teams;

use App\Contracts\Action;
use App\Models\Team;
use App\Models\User;

final class SwitchTeamAction implements Action
{
    /**
     * Switch user's active team
     *
     * @param  User  $user
     * @param  Team  $team
     */
    public function execute(mixed ...$params): User
    {
        $user = $params[0];
        $team = $params[1];

        $user->switchTeam($team);

        return $user->fresh();
    }
}
