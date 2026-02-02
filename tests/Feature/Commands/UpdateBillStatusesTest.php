<?php

use App\Models\Bill;
use App\Models\Category;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::create(['name' => 'Test Team', 'user_id' => $this->user->id]);
    $this->user->teams()->attach($this->team);
    $this->user->switchTeam($this->team);

    $this->category = Category::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
    ]);
});

test('command runs successfully with no bills', function () {
    $this->artisan('bills:update-statuses')
        ->expectsOutput('Starting bill status update...')
        ->expectsOutput('Processing 0 bills...')
        ->assertExitCode(0);
});

test('command updates recurring bill status to overdue when not in current period', function () {
    // Create a recurring monthly bill with due date in last month (not current period)
    $lastMonth = now()->subMonth();
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => $lastMonth->copy()->setDay(15),
        'status' => 'paid', // Set to paid initially
    ]);

    // Run the command
    $this->artisan('bills:update-statuses')
        ->assertExitCode(0);

    // Bill should be overdue since it's past the due date and not in the current period
    $bill->refresh();
    expect($bill->getAttributes()['status'])->toBe('overdue');
});

test('command keeps status as paid when recurring bill has transaction in current period', function () {
    // Create a recurring monthly bill with due date in current month
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => now()->setDay(15),
        'status' => 'unpaid',
    ]);

    // Switch to the team context
    $this->user->switchTeam($this->team);
    $this->actingAs($this->user);

    // Add a transaction in current month with payment_date
    $transaction = Transaction::factory()->create([
        'bill_id' => $bill->id,
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'amount' => $bill->amount,
        'payment_date' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Verify transaction exists (bypass global scopes)
    expect($bill->transactions()->withoutGlobalScopes()->count())->toBe(1);

    // Run the command
    $this->artisan('bills:update-statuses')
        ->assertExitCode(0);

    // Bill should be paid since it has a transaction in the current period
    $bill->refresh();
    expect($bill->getAttributes()['status'])->toBe('paid');
});

test('command does not modify non-recurring bills', function () {
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => false,
        'recurrence_period' => null,
        'due_date' => now()->addDays(5),
        'status' => 'unpaid',
    ]);

    $this->artisan('bills:update-statuses')
        ->assertExitCode(0);

    // Status should remain unchanged
    $bill->refresh();
    expect($bill->getAttributes()['status'])->toBe('unpaid');
});

test('command works with dry run flag', function () {
    $lastMonth = now()->subMonth();
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => $lastMonth->copy()->setDay(15),
        'status' => 'paid',
    ]);

    $this->artisan('bills:update-statuses', ['--dry-run' => true])
        ->expectsOutput('Running in DRY RUN mode - no changes will be made')
        ->expectsOutput('This was a DRY RUN - no changes were made to the database')
        ->assertExitCode(0);

    // Status should remain unchanged in dry-run mode (still 'paid' even though calculated would be 'overdue')
    $bill->refresh();
    expect($bill->getAttributes()['status'])->toBe('paid');
});

test('command filters by specific bill IDs', function () {
    $bill1 = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => now()->subMonth()->setDay(15),
        'status' => 'paid',
    ]);

    $bill2 = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => now()->subMonth()->setDay(20),
        'status' => 'paid',
    ]);

    $this->artisan('bills:update-statuses', ['--bill-id' => [$bill1->id]])
        ->expectsOutputToContain("Filtering by bill IDs: {$bill1->id}")
        ->assertExitCode(0);

    // Only bill1 should be updated to overdue
    $bill1->refresh();
    $bill2->refresh();
    expect($bill1->getAttributes()['status'])->toBe('overdue');
    expect($bill2->getAttributes()['status'])->toBe('paid');
});

test('command handles weekly recurring bills correctly', function () {
    // Create a weekly recurring bill due last week
    $lastWeek = now()->subWeek();
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'weekly',
        'due_date' => $lastWeek->copy()->setWeekday(1), // Monday of last week
        'status' => 'paid',
    ]);

    // No transaction in current week
    $this->artisan('bills:update-statuses')
        ->assertExitCode(0);

    $bill->refresh();
    expect($bill->getAttributes()['status'])->toBe('overdue');
});

test('command handles yearly recurring bills correctly', function () {
    // Create a yearly recurring bill due last year
    $lastYear = now()->subYear();
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'yearly',
        'due_date' => $lastYear->copy()->setMonth(6)->setDay(15),
        'status' => 'paid',
    ]);

    $this->artisan('bills:update-statuses')
        ->assertExitCode(0);

    $bill->refresh();
    expect($bill->getAttributes()['status'])->toBe('overdue');
});

test('command shows detailed information with show-details flag', function () {
    $lastMonth = now()->subMonth();
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'title' => 'Test Bill',
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => $lastMonth->copy()->setDay(15),
        'status' => 'paid',
    ]);

    $this->artisan('bills:update-statuses', ['--show-details' => true])
        ->expectsOutputToContain("Bill #{$bill->id} - Test Bill")
        ->expectsOutputToContain('Current: paid → Calculated: overdue')
        ->expectsOutputToContain('Recurring: monthly')
        ->assertExitCode(0);
});

test('command displays summary statistics', function () {
    Bill::factory()->count(3)->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => now()->subMonth()->setDay(15),
        'status' => 'paid',
    ]);

    $this->artisan('bills:update-statuses')
        ->expectsOutput('✓ Status update completed!')
        ->expectsOutputToContain('Total Bills Processed')
        ->expectsOutputToContain('Bills Updated')
        ->expectsOutputToContain('Bills Unchanged')
        ->assertExitCode(0);
});

test('command handles bills in current period correctly without transaction', function () {
    // Create a monthly recurring bill due in current month without transaction
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => now()->setDay(15),
        'status' => 'paid',
    ]);

    $this->artisan('bills:update-statuses')
        ->assertExitCode(0);

    // Status should be unpaid since it's in the current period but has no transaction
    $bill->refresh();
    expect($bill->getAttributes()['status'])->toBe('unpaid');
});

test('command handles future recurring bills correctly', function () {
    // Create a monthly recurring bill due next month
    $nextMonth = now()->addMonth();
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => $nextMonth->copy()->setDay(15),
        'status' => 'unpaid',
    ]);

    $this->artisan('bills:update-statuses')
        ->assertExitCode(0);

    // Status should be paid for future bills (not yet due)
    $bill->refresh();
    expect($bill->getAttributes()['status'])->toBe('paid');
});

test('command processes multiple bills efficiently', function () {
    // Create multiple bills with different scenarios
    $bills = collect();

    // 5 recurring bills from last month (should be overdue)
    for ($i = 0; $i < 5; $i++) {
        $bills->push(Bill::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'is_recurring' => true,
            'recurrence_period' => 'monthly',
            'due_date' => now()->subMonth()->setDay(10 + $i),
            'status' => 'paid',
        ]));
    }

    // 3 non-recurring bills (should stay unchanged)
    for ($i = 0; $i < 3; $i++) {
        $bills->push(Bill::factory()->create([
            'team_id' => $this->team->id,
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'is_recurring' => false,
            'due_date' => now()->addDays($i),
            'status' => 'unpaid',
        ]));
    }

    $this->artisan('bills:update-statuses')
        ->expectsOutput('Processing 8 bills...')
        ->assertExitCode(0);

    // Verify the updates - recurring bills should be overdue
    $bills->take(5)->each(function ($bill) {
        $bill->refresh();
        expect($bill->getAttributes()['status'])->toBe('overdue');
    });

    // Non-recurring bills stay unchanged
    $bills->slice(5)->each(function ($bill) {
        $bill->refresh();
        expect($bill->getAttributes()['status'])->toBe('unpaid');
    });
});
