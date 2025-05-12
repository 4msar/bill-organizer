<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\UpcomingBillNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SendUpcomingBillNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Get all users with notification preferences
        $users = User::query()->whereHas(
            'meta',
            fn (Builder $query) => $query
                ->where('email_notification', true)
                ->orWhere('web_notification', true),
        )
            ->with('bills')
            ->get();

        foreach ($users as $user) {
            $preferences = $user->getMeta('early_reminder_days', []);

            foreach ($preferences as $daysBefore) {
                // Get bills for the user due on the target date
                $bills = $user->bills
                    ->where('status', 'unpaid')
                    ->filter(fn ($bill) => $bill->isUpcomingIn($daysBefore))
                    ->all();

                foreach ($bills as $bill) {
                    $user->notify(new UpcomingBillNotification($bill));
                }
            }
        }
    }
}
