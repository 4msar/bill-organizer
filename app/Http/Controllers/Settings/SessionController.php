<?php

namespace App\Http\Controllers\Settings;

use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SessionController extends Controller
{

    public function sessions(Request $request): Response
    {
        $user = $request->user();

        /** --------------------
         * Web (SPA) Sessions
         * -------------------*/
        $webSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'type' => 'web',
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'is_current' => $session->id === session()->getId(),
                ];
            });

        /** --------------------
         * API Token Sessions
         * -------------------*/
        $apiSessions = $user->tokens->map(function ($token) {
            return [
                'id' => $token->id,
                'type' => 'api',
                'name' => $token->name,
                'last_activity' => optional($token->last_used_at)->diffForHumans() ?? 'Never',
                'expiry' => $token->expires_at?->diffForHumans() ?? 'Never',
                'created_at' => $token->created_at->diffForHumans(),
            ];
        });

        return Inertia::render('settings/Sessions', [
            'webSessions' => $webSessions,
            'apiSessions' => $apiSessions,
        ]);
    }

    public function revoke(Request $request)
    {
        $user = $request->user();

        if ($request->has('clear_all')) {
            // Revoke all sessions except current
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', session()->getId())
                ->delete();

            // Revoke all API tokens
            $user->tokens()->delete();

            return back()->with('status', 'All sessions revoked except current.');
        }

        $sessionId = $request->input('session_id');
        $sessionType = $request->input('session_type');

        if ($sessionType === 'web') {
            // Revoke web session
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', $sessionId)
                ->delete();

            return back()->with('status', 'Web session revoked.');
        } elseif ($sessionType === 'api') {
            // Revoke API token
            $user->tokens()->where('id', $sessionId)->delete();

            return back()->with('status', 'API token revoked.');
        }

        return back()->with('error', 'Invalid session type.');
    }
}
