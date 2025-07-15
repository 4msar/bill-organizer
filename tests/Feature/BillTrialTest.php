<?php

use App\Models\Bill;
use App\Models\Team;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::create(['name' => 'Test Team', 'user_id' => $this->user->id]);
    $this->user->teams()->attach($this->team);
    $this->user->switchTeam($this->team);
});

test('bill can have trial functionality enabled', function () {
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now(),
        'trial_end_date' => now()->addDays(14),
        'trial_status' => 'active',
    ]);

    expect($bill->has_trial)->toBeTrue();
    expect($bill->trial_status)->toBe('active');
    expect($bill->isInTrial())->toBeTrue();
});

test('bill trial status is correctly identified', function () {
    // Active trial
    $activeTrial = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(5),
        'trial_end_date' => now()->addDays(5),
        'trial_status' => 'active',
    ]);

    // Expired trial
    $expiredTrial = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(20),
        'trial_end_date' => now()->subDays(5),
        'trial_status' => 'active',
    ]);

    expect($activeTrial->isInTrial())->toBeTrue();
    expect($activeTrial->hasTrialExpired())->toBeFalse();
    
    expect($expiredTrial->isInTrial())->toBeFalse();
    expect($expiredTrial->hasTrialExpired())->toBeTrue();
});

test('trial ending soon is correctly detected', function () {
    // Trial ending in 2 days
    $endingSoon = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(10),
        'trial_end_date' => now()->addDays(2),
        'trial_status' => 'active',
    ]);

    // Trial ending in 10 days
    $notEndingSoon = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(5),
        'trial_end_date' => now()->addDays(10),
        'trial_status' => 'active',
    ]);

    expect($endingSoon->isTrialEndingSoon(3))->toBeTrue();
    expect($notEndingSoon->isTrialEndingSoon(3))->toBeFalse();
});

test('trial can be converted to regular bill', function () {
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(5),
        'trial_end_date' => now()->addDays(5),
        'trial_status' => 'active',
        'status' => 'unpaid',
    ]);

    $bill->convertTrialToRegular();
    $bill->refresh();

    expect($bill->trial_status)->toBe('converted');
    expect($bill->status)->toBe('unpaid');
});

test('trial can be marked as expired', function () {
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(20),
        'trial_end_date' => now()->subDays(5),
        'trial_status' => 'active',
    ]);

    $bill->markTrialAsExpired();
    $bill->refresh();

    expect($bill->trial_status)->toBe('expired');
});

test('bills in active trial should not trigger regular notifications', function () {
    $trialBill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(5),
        'trial_end_date' => now()->addDays(5),
        'trial_status' => 'active',
        'due_date' => now()->addDays(1), // Due tomorrow
        'status' => 'unpaid',
    ]);

    // Should not notify for regular bill reminders while in trial
    expect($trialBill->shouldNotify(1))->toBeFalse();
});

test('trial notification triggers are correct', function () {
    // Trial ending in 3 days (should trigger notification)
    $trialEnding3Days = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(10),
        'trial_end_date' => now()->addDays(3),
        'trial_status' => 'active',
    ]);

    // Trial ending in 1 day (should trigger notification)
    $trialEnding1Day = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(13),
        'trial_end_date' => now()->addDays(1),
        'trial_status' => 'active',
    ]);

    // Trial ending in 5 days (should NOT trigger notification for 3-day reminder)
    $trialEnding5Days = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'has_trial' => true,
        'trial_start_date' => now()->subDays(9),
        'trial_end_date' => now()->addDays(5),
        'trial_status' => 'active',
    ]);

    expect($trialEnding3Days->shouldNotifyForTrial(3))->toBeTrue();
    expect($trialEnding1Day->shouldNotifyForTrial(1))->toBeTrue();
    expect($trialEnding5Days->shouldNotifyForTrial(3))->toBeFalse();
});