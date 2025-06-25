<?php

namespace App\Http\Middleware;

use App\Enums\Status;
use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserHasTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        if ($user) {
            if ($user?->teams?->count() <= 0) {
                return to_route('team.create')->with('info', 'You must create a team first.');
            }

            if (!$user->activeTeam) {
                $user->switchTeam($user->teams->first());
            }
        }

        return $next($request);
    }
}
