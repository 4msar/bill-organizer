<?php

use App\Jobs\SendUpcomingBillNotifications;
use Illuminate\Support\Facades\Schedule;

/**
 * Send upcoming bill notifications to users.
 *
 * Run the job every minute.
 */
Schedule::job(SendUpcomingBillNotifications::class)->everyMinute();
