<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Content-Type', 'application/json');

        if (
            ! $request->expectsJson() ||
            $request->headers->get('Accept') !== '*/*'
        ) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
