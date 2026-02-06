<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserHasTeam
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
                if (! $request->expectsJson()) {
                    return to_route('team.create')
                        ->with('info', 'You must create a team first.');
                }

                return response()->json([
                    'status' => 403,
                    'message' => 'You must create a team first.',
                ], 403);
            }

            if (! $user->activeTeam) {
                $user->switchTeam($user->teams->first());
            }
        }

        return $next($request);
    }
}
