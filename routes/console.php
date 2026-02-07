<?php

use App\Jobs\SendUpcomingBillNotifications;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('app:send-upcoming-bill-notifications', function () {
    $this->comment('Sending upcoming bill notifications...');
    SendUpcomingBillNotifications::dispatch();
    $this->info('Upcoming bill notifications sent successfully.');
})->describe('Send upcoming bill notifications to users');

Artisan::command('app:update-slugs', function () {
    $this->comment('Updating slugs for all relevant models...');

    // Example for Bill model
    \App\Models\Bill::withoutGlobalScopes()->get()->each(function ($bill) {
        $bill->fillSlug();
        $this->info('Updated slug for Bill: ' . $bill->title . ' to ' . $bill->slug);
        $bill->save();
    });

    $this->comment("\n==============================\n");

    // Example for Team model
    \App\Models\Team::withoutGlobalScopes()->get()->each(function ($team) {
        $team->fillSlug();
        $this->info('Updated slug for Team: ' . $team->name . ' to ' . $team->slug);
        $team->save();
    });

    $this->info('Slugs updated successfully.');
})->describe('Update slugs for all relevant models');

/**
 * ===============================
 * Scheduler Commands
 * ===============================
 */

/**
 * Send upcoming bill notifications to users.
 *
 * In production, consider running it less frequently (e.g., every six hours)
 * to avoid spamming users with notifications.
 */
$sendUpcomingNotification = Schedule::command('app:send-upcoming-bill-notifications');

if (app()->isProduction()) {
    // In production, run every six hours to avoid spamming users
    $sendUpcomingNotification->everySixHours();
} else {
    // In development, run every minute to test notifications
    $sendUpcomingNotification->everyMinute();
}
$sendUpcomingNotification->runInBackground();

/**
 * Update bill statuses based on recurrence period and transactions.
 *
 * Run daily at 12:05 AM to ensure statuses are up-to-date.
 */
Schedule::command('bills:update-statuses')
    ->dailyAt('00:05')
    ->runInBackground();

/**
 * Clear expired API tokens to maintain security and performance.
 *
 * Run daily at 1:00 AM to clean up expired tokens.
 */
Schedule::command('sanctum:prune-expired')
    ->dailyAt('01:00')
    ->runInBackground();
