<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

if (app()->environment('local', 'dev', 'development')) {
    Route::any('/test', function (Request $request) {
        // dd($request->server('HTTP_X_FORWARDED_PROTO'));

        // dd($request->url());

        // preview team invite email templates
        // return new \App\Mail\TeamInvitation(
        //     \App\Models\Team::withoutGlobalScopes()->first(),
        //     \App\Models\User::first()
        // )->render();

        // preview upcoming bill notification email template
        // return (new \App\Notifications\UpcomingBillNotification(
        //     \App\Models\Bill::withoutGlobalScopes()->with('team')->first()
        // ))->toMail(\App\Models\User::first());

        // return (new \App\Notifications\TrialEndNotification(
        //     \App\Models\Bill::withoutGlobalScopes()->with('team')->first()
        // ))->toMail(\App\Models\User::first());

        return "test route works";
    });
}
