<?php

use App\Enums\WebhookEvent;
use App\Jobs\DispatchWebhookJob;
use App\Models\Team;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->withTeam()->create();
    $this->user->refresh();
    $this->team = Team::withoutGlobalScopes()->find($this->user->active_team_id);
    $this->token = $this->user->createToken('test')->plainTextToken;
});

// --- Index ---

test('can list webhooks', function () {
    Webhook::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/webhooks');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'url', 'events', 'is_active'],
            ],
            'meta',
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('webhooks are team scoped', function () {
    $otherUser = User::factory()->withTeam()->create();
    $otherTeam = Team::withoutGlobalScopes()->find($otherUser->active_team_id);

    Webhook::factory()->create([
        'user_id' => $otherUser->id,
        'team_id' => $otherTeam->id,
        'name' => 'Other Team Webhook',
    ]);

    Webhook::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'My Webhook',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/webhooks');

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name');
    expect($names)->not->toContain('Other Team Webhook');
    expect($names)->toContain('My Webhook');
});

// --- Store ---

test('can create webhook', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/webhooks', [
            'name' => 'My Webhook',
            'url' => 'https://example.com/webhook',
            'events' => [WebhookEvent::BillingCreated->value, WebhookEvent::BillingPaid->value],
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'url', 'events', 'is_active'],
        ]);

    $this->assertDatabaseHas('webhooks', [
        'name' => 'My Webhook',
        'url' => 'https://example.com/webhook',
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
    ]);
});

test('webhook is created with a secret', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/webhooks', [
            'name' => 'Signed Webhook',
            'url' => 'https://example.com/hook',
            'events' => [WebhookEvent::BillingCreated->value],
        ]);

    $response->assertCreated();

    $webhook = Webhook::find($response->json('data.id'));
    expect($webhook->secret)->not->toBeEmpty();
});

test('validates required fields when creating webhook', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/webhooks', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'url', 'events']);
});

test('validates url format when creating webhook', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/webhooks', [
            'name' => 'Bad URL',
            'url' => 'not-a-url',
            'events' => [WebhookEvent::BillingCreated->value],
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('validates event values when creating webhook', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/webhooks', [
            'name' => 'Bad Events',
            'url' => 'https://example.com/hook',
            'events' => ['not.an.event'],
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['events.0']);
});

// --- Show ---

test('can show webhook', function () {
    $webhook = Webhook::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/webhooks/{$webhook->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => ['id' => $webhook->id],
        ]);
});

test('cannot show webhook belonging to another team', function () {
    $otherUser = User::factory()->withTeam()->create();
    $otherTeam = Team::withoutGlobalScopes()->find($otherUser->active_team_id);

    $webhook = Webhook::factory()->create([
        'user_id' => $otherUser->id,
        'team_id' => $otherTeam->id,
    ]);

    $this->withToken($this->token)
        ->getJson("/api/v1/webhooks/{$webhook->id}")
        ->assertForbidden();
});

// --- Update ---

test('can update webhook', function () {
    $webhook = Webhook::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Old Name',
    ]);

    $response = $this->withToken($this->token)
        ->putJson("/api/v1/webhooks/{$webhook->id}", [
            'name' => 'New Name',
            'is_active' => false,
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Webhook updated successfully',
            'data' => ['name' => 'New Name', 'is_active' => false],
        ]);

    $this->assertDatabaseHas('webhooks', [
        'id' => $webhook->id,
        'name' => 'New Name',
        'is_active' => false,
    ]);
});

// --- Destroy ---

test('can delete webhook', function () {
    $webhook = Webhook::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $this->withToken($this->token)
        ->deleteJson("/api/v1/webhooks/{$webhook->id}")
        ->assertOk()
        ->assertJson(['success' => true, 'message' => 'Webhook deleted successfully']);

    $this->assertDatabaseMissing('webhooks', ['id' => $webhook->id]);
});

// --- Events ---

test('can list available webhook events', function () {
    $response = $this->withToken($this->token)
        ->getJson('/api/v1/webhooks/events');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['value', 'label'],
            ],
        ]);

    $values = collect($response->json('data'))->pluck('value')->toArray();
    expect($values)->toContain(WebhookEvent::BillingCreated->value);
    expect($values)->toContain(WebhookEvent::BillingPaid->value);
    expect($values)->toContain(WebhookEvent::TransactionCreated->value);
});

// --- Deliveries ---

test('can list webhook deliveries', function () {
    $webhook = Webhook::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    WebhookDelivery::factory()->count(2)->create([
        'webhook_id' => $webhook->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/webhooks/{$webhook->id}/deliveries");

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'event', 'status', 'attempts'],
            ],
            'meta',
        ]);

    expect($response->json('data'))->toHaveCount(2);
});

// --- Retry ---

test('can retry a failed delivery', function () {
    Queue::fake();

    $webhook = Webhook::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'events' => [WebhookEvent::BillingCreated->value],
    ]);

    $delivery = WebhookDelivery::factory()->create([
        'webhook_id' => $webhook->id,
        'event' => WebhookEvent::BillingCreated->value,
        'status' => 'failed',
        'payload' => ['id' => 1],
    ]);

    $this->withToken($this->token)
        ->postJson("/api/v1/webhooks/{$webhook->id}/deliveries/{$delivery->id}/retry")
        ->assertOk()
        ->assertJson(['success' => true]);

    Queue::assertPushed(DispatchWebhookJob::class);
});

// --- Auth / Team ---

test('cannot access webhooks without authentication', function () {
    $this->getJson('/api/v1/webhooks')
        ->assertUnauthorized();
});

test('cannot access webhooks without team', function () {
    $userWithoutTeam = User::factory()->create();
    $token = $userWithoutTeam->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/webhooks')
        ->assertForbidden();
});
