<?php

use App\Jobs\SendUpcomingBillNotifications;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/**
 * Send upcoming bill notifications to users.
 *
 * Run the job every minute.
 */
$schedule = Schedule::job(SendUpcomingBillNotifications::class);

if (app()->isProduction()) {
    // In production, run daily at 6 AM to avoid spamming users
    $schedule->dailyAt('06:00');
} else {
    // In development, run every minute to test notifications
    $schedule->everyMinute();
}

Artisan::command('app:test', function () {
    // $day = $this->ask('Day:');
    $user = \App\Models\User::find(1);
    // $bills = $user->bills
    //     ->where('status', 'unpaid')
    //     ->filter(fn($bill) => $bill->isUpcomingIn($day))->values();

    $bills = $user->bills
        ->where('status', 'unpaid')
        ->filter(fn($bill) => $bill->shouldNotify(1))
        ->values();

    dd($bills->toArray());
});
