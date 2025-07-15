<?php

use App\Jobs\SendUpcomingBillNotifications;
use App\Models\Bill;
use App\Models\Team;
use App\Models\User;
use App\Notifications\TrialEndingNotification;
use App\Notifications\TrialExpiredNotification;
use App\Notifications\UpcomingBillNotification;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::create(['name' => 'Test Team', 'user_id' => $this->user->id]);
    $this->user->teams()->attach($this->team);
    $this->user->switchTeam($this->team);
    
    // Set up user notification preferences
    $this->user->setMeta('email_notification', true);
    $this->user->setMeta('web_notification', true);
    $this->user->setMeta('early_reminder_days', [7, 3, 1]);

    // Authenticate as the user for team scope to work
    $this->actingAs($this->user);
});

test('job sends trial ending notification when trial is ending in 3 days', function () {
    Notification::fake();

    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(10),
        'trial_end_date' => now()->addDays(3),
        'trial_status' => 'active',
    ]);

    $job = new SendUpcomingBillNotifications();
    $job->handle();

    Notification::assertSentTo($this->user, TrialEndingNotification::class, function ($notification, $notifiable) use ($bill) {
        return $notification->toArray($notifiable)['bill_id'] === $bill->id;
    });
});

test('job sends trial ending notification when trial is ending in 1 day', function () {
    Notification::fake();

    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(13),
        'trial_end_date' => now()->addDays(1),
        'trial_status' => 'active',
    ]);

    $job = new SendUpcomingBillNotifications();
    $job->handle();

    Notification::assertSentTo($this->user, TrialEndingNotification::class, function ($notification, $notifiable) use ($bill) {
        return $notification->toArray($notifiable)['bill_id'] === $bill->id;
    });
});

test('job sends trial expired notification for expired trials', function () {
    Notification::fake();

    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(20),
        'trial_end_date' => now()->subDays(1),
        'trial_status' => 'active',
    ]);

    $job = new SendUpcomingBillNotifications();
    $job->handle();

    Notification::assertSentTo($this->user, TrialExpiredNotification::class, function ($notification, $notifiable) use ($bill) {
        return $notification->toArray($notifiable)['bill_id'] === $bill->id;
    });

    // Verify bill status was updated
    $bill->refresh();
    expect($bill->trial_status)->toBe('expired');
});

test('job does not send regular bill notifications for bills in active trial', function () {
    Notification::fake();

    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(5),
        'trial_end_date' => now()->addDays(5),
        'trial_status' => 'active',
        'due_date' => now()->addDays(3), // Should trigger notification if not in trial
        'status' => 'unpaid',
    ]);

    $job = new SendUpcomingBillNotifications();
    $job->handle();

    // Should not send regular bill notification
    Notification::assertNotSentTo($this->user, UpcomingBillNotification::class);
});

test('job sends regular bill notifications for converted trial bills', function () {
    Notification::fake();

    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(20),
        'trial_end_date' => now()->subDays(5),
        'trial_status' => 'converted',
        'due_date' => now()->addDays(3), // Should trigger notification
        'status' => 'unpaid',
    ]);

    $job = new SendUpcomingBillNotifications();
    $job->handle();

    // Should send regular bill notification for converted trial
    Notification::assertSentTo($this->user, UpcomingBillNotification::class, function ($notification, $notifiable) use ($bill) {
        return $notification->toArray($notifiable)['bill_id'] === $bill->id;
    });
});

test('job does not send duplicate trial notifications', function () {
    Notification::fake();

    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(10),
        'trial_end_date' => now()->addDays(3),
        'trial_status' => 'active',
    ]);

    // Mark as already notified for trial_ending_3
    $bill->markAsNotified('trial_ending_3', ['mail', 'database']);

    $job = new SendUpcomingBillNotifications();
    $job->handle();

    // Should not send duplicate notification
    Notification::assertNotSentTo($this->user, TrialEndingNotification::class);
});

test('job handles multiple bills with different trial statuses', function () {
    Notification::fake();

    // Trial ending in 3 days
    $trialEnding = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(10),
        'trial_end_date' => now()->addDays(3),
        'trial_status' => 'active',
    ]);

    // Expired trial
    $expiredTrial = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(20),
        'trial_end_date' => now()->subDays(1),
        'trial_status' => 'active',
    ]);

    // Regular bill (no trial)
    $regularBill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => false,
        'due_date' => now()->addDays(3),
        'status' => 'unpaid',
    ]);

    $job = new SendUpcomingBillNotifications();
    $job->handle();

    // Should send trial ending notification
    Notification::assertSentTo($this->user, TrialEndingNotification::class, function ($notification, $notifiable) use ($trialEnding) {
        return $notification->toArray($notifiable)['bill_id'] === $trialEnding->id;
    });

    // Should send trial expired notification
    Notification::assertSentTo($this->user, TrialExpiredNotification::class, function ($notification, $notifiable) use ($expiredTrial) {
        return $notification->toArray($notifiable)['bill_id'] === $expiredTrial->id;
    });

    // Should send regular bill notification
    Notification::assertSentTo($this->user, UpcomingBillNotification::class, function ($notification, $notifiable) use ($regularBill) {
        return $notification->toArray($notifiable)['bill_id'] === $regularBill->id;
    });
});