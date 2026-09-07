<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class SwitchTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $teamId = $request->get('team');
        $token = $request->get('team_token');

        if (empty($teamId) || empty($token)) {
            return $next($request);
        }

        if ($request->user() && Hash::check($teamId, $token)) {
            $request->user()->switchTeam($teamId);
        }

        return $next($request);
    }
}
