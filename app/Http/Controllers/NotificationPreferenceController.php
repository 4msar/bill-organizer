<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

final class NotificationPreferenceController extends Controller
{
    /**
     * Show the notification preferences form.
     */
    public function edit()
    {
        $user = Auth::user();

        return Inertia::render('settings/NotificationPreferences', [
            'preferences' => $user->notification_preferences ? json_decode($user->notification_preferences, true) : [],
        ]);
    }

    /**
     * Update the notification preferences.
     */
    public function update(Request $request)
    {
        $request->validate([
            'email_notification' => ['nullable', 'boolean'],
            'web_notification' => ['nullable', 'boolean'],
            'early_reminder_days' => ['array'],
        ]);

        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        $user->setMeta('email_notification', $request->input('email_notification', false));
        $user->setMeta('web_notification', $request->input('web_notification', false));
        $user->setMeta('early_reminder_days', $request->input('early_reminder_days', []));

        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }
}
