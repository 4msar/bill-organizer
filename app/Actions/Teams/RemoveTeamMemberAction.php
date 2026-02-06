<?php

namespace App\Actions\Teams;

use App\Contracts\Action;
use App\Models\Team;
use App\Models\User;

final class RemoveTeamMemberAction implements Action
{
    /**
     * Remove a member from the team
     *
     * @param  Team  $team
     * @param  User  $member
     */
    public function execute(mixed ...$params): void
    {
        $team = $params[0];
        $member = $params[1];

        // Check if the member is the owner of the team
        if ($member->id === $team->user_id) {
            throw new \InvalidArgumentException('Cannot remove the team owner.');
        }

        $team->users()->detach($member);
    }
}
