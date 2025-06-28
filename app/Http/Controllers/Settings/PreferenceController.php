<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

final class PreferenceController extends Controller
{
    /**
     * Show the notification preferences form.
     */
    public function edit()
    {
        $user = Auth::user();
        $user->load('meta');

        return Inertia::render('settings/Preferences');
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
            'enable_notes' => ['nullable', 'boolean'],
            'enable_calendar' => ['nullable', 'boolean'],
        ]);

        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        // Update notification preferences
        $user->setMeta('email_notification', $request->input('email_notification', false));
        $user->setMeta('web_notification', $request->input('web_notification', false));
        $user->setMeta('early_reminder_days', $request->input('early_reminder_days', []));

        // Enable or disable features
        $user->setMeta('enable_notes', $request->input('enable_notes', false));
        $user->setMeta('enable_calendar', $request->input('enable_calendar', false));

        return redirect()->back()->with('success', 'Application preferences updated successfully.');
    }
}
