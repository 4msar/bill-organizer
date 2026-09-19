<?php

use App\Jobs\SendUpcomingBillNotifications;
use Illuminate\Support\Facades\Artisan;

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

Artisan::command('app:clear-notifications', function () {
    $this->comment("Clearing all notifications meta for bills...\n");

    // Clear notifications meta for bills
    \App\Models\Bill::withoutGlobalScopes()->get()->each(function ($bill) {
        $bill->clearNotificationsMeta();
        $this->info('Cleared notifications meta for Bill: ' . $bill->title);
    });
})->describe('Clear all notifications meta for bills.');
