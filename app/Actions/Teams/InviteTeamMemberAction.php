<?php

namespace App\Actions\Teams;

use App\Contracts\Action;
use App\Mail\TeamInvitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class InviteTeamMemberAction implements Action
{
    /**
     * Invite a member to the team
     *
     * @param  Team  $team
     * @param  User  $inviter
     * @param  string  $email
     */
    public function execute(mixed ...$params): bool
    {
        $team = $params[0];
        $inviter = $params[1];
        $email = $params[2];

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
}
