<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationPreferenceController extends Controller
{
    /**
     * Show the notification preferences form.
     */
    public function edit()
    {
        $user = Auth::user();

        return Inertia::render('Profile/NotificationPreferences', [
            'preferences' => $user->notification_preferences ? json_decode($user->notification_preferences, true) : [],
        ]);
    }

    /**
     * Update the notification preferences.
     */
    public function update(Request $request)
    {
        $request->validate([
            'preferences' => 'required|array',
            'preferences.*' => 'integer|in:1,3,7', // Only allow 1, 3, or 7 days before
        ]);

        $user = Auth::user();
        $user->notification_preferences = json_encode($request->preferences);
        $user->save();

        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }
}
