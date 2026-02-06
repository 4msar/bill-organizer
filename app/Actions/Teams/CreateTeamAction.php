<?php

namespace App\Actions\Teams;

use App\Contracts\Action;
use App\Enums\Status;
use App\Models\Team;
use App\Models\User;

final class CreateTeamAction implements Action
{
    /**
     * Create a new team
     *
     * @param  User  $user
     * @param  array  $data
     */
    public function execute(mixed ...$params): Team
    {
        $user = $params[0];
        $data = $params[1];

        $team = $user->teams()->create($data + [
            'user_id' => $user->id,
            'status' => Status::Active,
        ]);

        $user->teams()->attach($team);

        return $team;
    }
}
