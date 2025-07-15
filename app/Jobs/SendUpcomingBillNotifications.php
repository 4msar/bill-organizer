<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\TrialEndingNotification;
use App\Notifications\TrialExpiredNotification;
use App\Notifications\UpcomingBillNotification;
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
                $query->where(fn ($q) => $q->where('name', 'email_notification')->where('value', '1'));

                $query->orWhere(fn ($q) => $q->where('name', 'web_notification')->where('value', '1'));
            })
            ->with(['bills', 'meta', 'bills.meta'])
            ->get();

        foreach ($users as $user) {
            $this->handleTrialNotifications($user);
            $this->handleRegularBillNotifications($user);
        }
    }

    /**
     * Handle trial-related notifications for the user.
     */
    private function handleTrialNotifications(User $user): void
    {
        $channels = $user->getNotificationChannels();
        
        // Get bills with active trials (including recently expired ones)
        $trialBills = $user->bills
            ->where('has_trial', true)
            ->where('trial_status', 'active')
            ->filter(fn ($bill) => $bill->trial_end_date !== null)
            ->values();

        foreach ($trialBills as $bill) {
            // Check for expired trials first
            if ($bill->hasTrialExpired()) {
                // Skip if already notified for trial expiration
                if ($bill->isAlreadyNotified('trial_expired', $channels)) {
                    continue;
                }

                $user->notify(new TrialExpiredNotification($bill));
                $bill->markAsNotified('trial_expired', $channels);
                $bill->markTrialAsExpired();
                continue; // Skip trial ending notifications for expired trials
            }

            // Check for trial ending notifications (3 days, 1 day before) for active trials
            if (now()->lte($bill->trial_end_date)) {
                foreach ([3, 1] as $daysBefore) {
                    if ($bill->shouldNotifyForTrial($daysBefore)) {
                        // Skip if already notified for this trial period
                        if ($bill->isAlreadyNotified("trial_ending_{$daysBefore}", $channels)) {
                            continue;
                        }

                        $user->notify(new TrialEndingNotification($bill));
                        $bill->markAsNotified("trial_ending_{$daysBefore}", $channels);
                    }
                }
            }
        }
    }

    /**
     * Handle regular bill notifications for the user.
     */
    private function handleRegularBillNotifications(User $user): void
    {
        $preferences = $user->getMeta('early_reminder_days', []);
        $channels = $user->getNotificationChannels();

        foreach ($preferences as $daysBefore) {
            // Get bills for the user due on the target date
            $bills = $user->bills
                ->where('status', 'unpaid')
                ->filter(fn ($bill) => $bill->shouldNotify($daysBefore))
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
        }
    }
}
