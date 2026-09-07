<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('user can create an API token without an expiry date', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/settings/sessions/tokens', [
            'name' => 'My device',
            'expires_at' => null,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('token')
        ->assertRedirect();

    $token = PersonalAccessToken::query()->where('name', 'My device')->first();

    expect($token)->not->toBeNull()
        ->and($token->tokenable_id)->toBe($user->id)
        ->and($token->expires_at)->toBeNull();
});

test('API token name is required', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->post('/settings/sessions/tokens', ['expires_at' => null])
        ->assertSessionHasErrors('name');
});
