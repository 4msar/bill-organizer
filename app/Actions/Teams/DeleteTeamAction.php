<?php

namespace App\Actions\Teams;

use App\Contracts\Action;
use App\Models\Team;

final class DeleteTeamAction implements Action
{
    /**
     * Delete a team
     *
     * @param  Team  $team
     */
    public function execute(mixed ...$params): void
    {
        $team = $params[0];

        $team->delete();
    }
}
