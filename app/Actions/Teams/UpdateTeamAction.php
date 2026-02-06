<?php

namespace App\Actions\Teams;

use App\Contracts\Action;
use App\Models\Team;

final class UpdateTeamAction implements Action
{
    /**
     * Update a team
     *
     * @param  Team  $team
     * @param  array  $data
     */
    public function execute(mixed ...$params): Team
    {
        $team = $params[0];
        $data = $params[1];

        $team->update($data);

        return $team->fresh();
    }
}
