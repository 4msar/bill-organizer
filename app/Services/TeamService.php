<?php

namespace App\Services;

use App\Enums\Status;
use App\Mail\TeamInvitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

final class TeamService
{
    /**
     * Create a new team with file upload
     */
    public function createTeam(User $user, array $data): Team
    {
        return DB::transaction(function () use ($user, $data) {
            $team = $user->teams()->create($data + [
                'user_id' => $user->id,
                'status' => Status::Active,
            ]);

            $user->teams()->attach($team);

            $this->switchTeam($user, $team);

            return $team;
        });
    }

    /**
     * Update a team
     */
    public function updateTeam(Team $team, array $data): Team
    {
        $team->update($data);

        return $team->fresh();
    }

    /**
     * Delete a team
     */
    public function deleteTeam(Team $team): void
    {
        $team->delete();
    }

    /**
     * Switch user's active team
     */
    public function switchTeam(User $user, Team $team): User
    {
        $user->switchTeam($team);

        return $user->fresh();
    }

    /**
     * Invite a member to the team
     */
    public function inviteMember(Team $team, User $inviter, string $email): bool
    {
        // Check if the user already exists
        $member = User::where('email', $email)->first();

        if ($member) {
            // If the user exists, add them to the team
            $team->users()->attach($member);

            return true;
        }

        // Send invitation email
        try {
            Mail::to($email)->send(new TeamInvitation($team, $inviter, $email));

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Remove a member from the team
     */
    public function removeMember(Team $team, User $member): void
    {
        // Check if the member is the owner of the team
        if ($member->id === $team->user_id) {
            throw new \InvalidArgumentException('Cannot remove the team owner.');
        }

        $team->users()->detach($member);
    }

    /**
     * Remove team logo
     */
    public function removeLogo(Team $team): Team
    {
        if (
            $team->icon &&
            ! str($team->icon)->startsWith('http') &&
            Storage::exists($team->icon)
        ) {
            Storage::delete($team->icon);
        }

        $team->icon = null;
        $team->save();

        return $team;
    }
}
