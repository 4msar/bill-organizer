<?php

namespace App\Services;

use App\Actions\Teams\CreateTeamAction;
use App\Actions\Teams\DeleteTeamAction;
use App\Actions\Teams\InviteTeamMemberAction;
use App\Actions\Teams\RemoveTeamLogoAction;
use App\Actions\Teams\RemoveTeamMemberAction;
use App\Actions\Teams\SwitchTeamAction;
use App\Actions\Teams\UpdateTeamAction;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class TeamService
{
    public function __construct(
        private CreateTeamAction $createTeamAction,
        private UpdateTeamAction $updateTeamAction,
        private DeleteTeamAction $deleteTeamAction,
        private SwitchTeamAction $switchTeamAction,
        private InviteTeamMemberAction $inviteTeamMemberAction,
        private RemoveTeamMemberAction $removeTeamMemberAction,
        private RemoveTeamLogoAction $removeTeamLogoAction
    ) {}

    /**
     * Create a new team with file upload
     */
    public function createTeam(User $user, array $data): Team
    {
        return DB::transaction(function () use ($user, $data) {
            $team = $this->createTeamAction->execute($user, $data);

            $this->switchTeamAction->execute($user, $team);

            return $team;
        });
    }

    /**
     * Update a team
     */
    public function updateTeam(Team $team, array $data): Team
    {
        return $this->updateTeamAction->execute($team, $data);
    }

    /**
     * Delete a team
     */
    public function deleteTeam(Team $team): void
    {
        $this->deleteTeamAction->execute($team);
    }

    /**
     * Switch user's active team
     */
    public function switchTeam(User $user, Team $team): User
    {
        return $this->switchTeamAction->execute($user, $team);
    }

    /**
     * Invite a member to the team
     */
    public function inviteMember(Team $team, User $inviter, string $email): bool
    {
        return $this->inviteTeamMemberAction->execute($team, $inviter, $email);
    }

    /**
     * Remove a member from the team
     */
    public function removeMember(Team $team, User $member): void
    {
        $this->removeTeamMemberAction->execute($team, $member);
    }

    /**
     * Remove team logo
     */
    public function removeLogo(Team $team): Team
    {
        return $this->removeTeamLogoAction->execute($team);
    }
}
