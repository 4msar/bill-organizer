<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserCanAccessFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $feature): Response
    {
        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        if ($user) {
            $featureEnabled = $user->getMeta("enable_$feature", false);

            if (! $featureEnabled) {
                return back(fallback: route('dashboard'))
                    ->with('error', 'You do not have access to this feature.');
            }
        }

        return $next($request);
    }
}
