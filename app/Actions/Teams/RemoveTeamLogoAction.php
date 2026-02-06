<?php

namespace App\Actions\Teams;

use App\Contracts\Action;
use App\Models\Team;
use Illuminate\Support\Facades\Storage;

final class RemoveTeamLogoAction implements Action
{
    /**
     * Remove team logo
     *
     * @param  Team  $team
     */
    public function execute(mixed ...$params): Team
    {
        $team = $params[0];

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
