<?php

use App\Jobs\SendUpcomingBillNotifications;
use Illuminate\Support\Facades\Schedule;

Schedule::job(SendUpcomingBillNotifications::class)
    ->everyFiveSeconds();
