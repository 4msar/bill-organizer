<?php

use App\Models\Bill;
use App\Models\Category;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->withTeam()->create();
    $this->user->refresh();
    $this->team = Team::withoutGlobalScopes()->find($this->user->active_team_id);
    $this->token = $this->user->createToken('test')->plainTextToken;
});

test('can list bills', function () {
    Bill::factory()->count(5)->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/bills');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'amount',
                    'due_date',
                    'status',
                ],
            ],
            'links',
            'meta',
        ]);
});

test('can filter bills by status', function () {
    Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'status' => 'paid',
        'is_recurring' => false,
    ]);

    Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'status' => 'unpaid',
        'is_recurring' => false,
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/bills?status=paid');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.status'))->toBe('paid');
});

test('can search bills', function () {
    Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'title' => 'Netflix Subscription',
    ]);

    Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'title' => 'Spotify Premium',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/bills?search=Netflix');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.title'))->toContain('Netflix');
});

test('can create bill', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->postJson('/api/v1/bills', [
            'title' => 'New Bill',
            'amount' => 99.99,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'category_id' => $category->id,
            'description' => 'Test bill',
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'title', 'amount', 'due_date'],
        ]);

    $this->assertDatabaseHas('bills', [
        'title' => 'New Bill',
        'amount' => 99.99,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);
});

test('can show bill', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/bills/{$bill->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $bill->id,
                'title' => $bill->title,
            ],
        ]);
});

test('can update bill', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'title' => 'Old Title',
    ]);

    $response = $this->withToken($this->token)
        ->putJson("/api/v1/bills/{$bill->id}", [
            'title' => 'Updated Title',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Bill updated successfully',
        ]);

    $this->assertDatabaseHas('bills', [
        'id' => $bill->id,
        'title' => 'Updated Title',
    ]);
});

test('can delete bill', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/bills/{$bill->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Bill deleted successfully',
        ]);

    $this->assertDatabaseMissing('bills', ['id' => $bill->id]);
});

test('can mark bill as paid', function () {
    $bill = Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'status' => 'unpaid',
    ]);

    $response = $this->withToken($this->token)
        ->patchJson("/api/v1/bills/{$bill->id}/pay");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Bill marked as paid',
        ]);

    $this->assertDatabaseHas('bills', [
        'id' => $bill->id,
        'status' => 'paid',
    ]);
});

test('cannot access bills without authentication', function () {
    $response = $this->getJson('/api/v1/bills');

    $response->assertUnauthorized();
});

test('cannot access bills without team', function () {
    $userWithoutTeam = User::factory()->create();
    $token = $userWithoutTeam->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/bills');

    $response->assertStatus(403);
});
