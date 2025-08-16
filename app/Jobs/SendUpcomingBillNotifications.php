<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\UpcomingBillNotification;
use App\Notifications\TrialEndNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// $job = (new \App\Jobs\MyJob($data));

// dispatch($job);

final class SendUpcomingBillNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Get all users with notification preferences
        $users = User::query()
            ->whereHas('meta', function ($query) {
                $query->where(
                    fn($q) => $q->where('name', 'email_notification')
                        ->where('value', '1')
                );

                $query->orWhere(
                    fn($q) => $q->where('name', 'web_notification')
                        ->where('value', '1')
                );
            })
            ->with(['bills', 'meta', 'bills.meta', 'bills.team'])
            ->get();

        foreach ($users as $user) {
            $preferences = $user->getMeta('early_reminder_days', []);
            $channels = $user->getNotificationChannels();

            foreach ($preferences as $daysBefore) {
                // Get bills for the user due on the target date
                $bills = $user->bills
                    ->where('status', 'unpaid')
                    ->filter(fn($bill) => $bill->shouldNotify($daysBefore))
                    ->values();

                foreach ($bills as $bill) {
                    // Skip if the bill notification has already been sent for this reminder period
                    if ($bill->isAlreadyNotified($daysBefore, $channels)) {
                        continue;
                    }

                    // Notify the user about the upcoming bill
                    $user->notify(new UpcomingBillNotification($bill));

                    $bill->markAsNotified($daysBefore, $channels);
                }

                // Handle trial end notifications
                $trialBills = $user->bills
                    ->where('has_trial', true)
                    ->where('status', 'unpaid')
                    ->filter(fn($bill) => $bill->shouldNotifyTrialEnd($daysBefore))
                    ->values();

                foreach ($trialBills as $bill) {
                    // Skip if the trial end notification has already been sent for this reminder period
                    if ($bill->isAlreadyNotifiedTrialEnd($daysBefore, $channels)) {
                        continue;
                    }

                    // Notify the user about the trial ending
                    $user->notify(new TrialEndNotification($bill));

                    $bill->markAsNotifiedTrialEnd($daysBefore, $channels);
                }
            }
        }
    }
}
