<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PushSubscriptionController extends Controller
{
    /**
     * Store (or update) the authenticated user's push subscription for this device.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->updatePushSubscription(
            endpoint: $validated['endpoint'],
            key: $validated['keys']['p256dh'],
            token: $validated['keys']['auth'],
        );

        return response()->json(['status' => 'subscribed']);
    }

    /**
     * Remove the authenticated user's push subscription for this device.
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->deletePushSubscription($validated['endpoint']);

        return response()->json(['status' => 'unsubscribed']);
    }
}
