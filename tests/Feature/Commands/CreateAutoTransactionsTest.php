<?php

use App\Models\Bill;
use App\Models\BillAutoTransaction;
use App\Models\Category;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = Team::create([
        'name' => 'Test Team',
        'user_id' => $this->user->id,
    ]);
    $this->user->teams()->attach($this->team);
    $this->user->switchTeam($this->team);

    $this->category = Category::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
    ]);
});

test('it creates a transaction for due bill with auto transaction enabled', function () {
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'status' => 'unpaid',
        'is_recurring' => false,
        'due_date' => today(),
    ]);

    BillAutoTransaction::create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'bill_id' => $bill->id,
        'amount' => 150.25,
        'payment_method' => 'credit_card',
        'notes' => 'Auto payment',
        'is_active' => true,
    ]);

    $this->artisan('bills:create-auto-transactions', ['--date' => today()->toDateString()])
        ->assertExitCode(0);

    $this->assertDatabaseHas('transactions', [
        'bill_id' => $bill->id,
        'amount' => 150.25,
        'payment_method' => 'credit_card',
        'notes' => 'Auto payment',
    ]);

    $this->assertDatabaseHas('bills', [
        'id' => $bill->id,
        'status' => 'paid',
    ]);

    $this->assertDatabaseHas('bill_auto_transactions', [
        'bill_id' => $bill->id,
        'last_processed_date' => today()->format('Y-m-d H:i:s'),
    ]);
});

test('it updates due date for recurring bills after auto transaction is created', function () {
    $today = today();
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'status' => 'unpaid',
        'is_recurring' => true,
        'recurrence_period' => 'monthly',
        'due_date' => $today,
    ]);

    BillAutoTransaction::create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'bill_id' => $bill->id,
        'amount' => 49.99,
        'payment_method' => 'cash',
        'is_active' => true,
    ]);

    $this->artisan('bills:create-auto-transactions', ['--date' => $today->toDateString()])
        ->assertExitCode(0);

    $bill->refresh();

    expect($bill->due_date->toDateString())->toBe($today->copy()->addMonth()->toDateString());
    expect($bill->getRawOriginal('status'))->toBe('unpaid');
});

test('it does not create duplicate transactions for same bill and date', function () {
    $today = today();
    $bill = Bill::factory()->create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'status' => 'unpaid',
        'is_recurring' => false,
        'due_date' => $today,
    ]);

    BillAutoTransaction::create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'bill_id' => $bill->id,
        'amount' => 79.99,
        'payment_method' => 'paypal',
        'is_active' => true,
    ]);

    $this->artisan('bills:create-auto-transactions', ['--date' => $today->toDateString()])
        ->assertExitCode(0);
    $this->artisan('bills:create-auto-transactions', ['--date' => $today->toDateString()])
        ->assertExitCode(0);

    expect(
        Transaction::withoutGlobalScopes()
            ->where('bill_id', $bill->id)
            ->whereDate('payment_date', $today->toDateString())
            ->count()
    )->toBe(1);
});
