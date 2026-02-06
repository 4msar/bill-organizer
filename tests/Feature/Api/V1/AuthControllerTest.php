<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->withTeam()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);
});

test('user can login with valid credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'device_name' => 'test-device',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Login successful',
            'data' => ['token_type' => 'Bearer'],
        ]);
});

test('user cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
        'device_name' => 'test-device',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('user cannot login with non-existent email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('user can register with valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
                'token',
                'token_type',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
    ]);
});

test('user cannot register with existing email', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Another User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('user can logout', function () {
    $token = $this->user->createToken('test-device')->plainTextToken;

    $response = $this->withToken($token)
        ->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Logout successful',
        ]);

    // Verify token is deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $this->user->id,
        'name' => 'test-device',
    ]);
});

test('authenticated user can get their profile', function () {
    $token = $this->user->createToken('test-device')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/auth/user');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'active_team_id',
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'email' => 'test@example.com',
            ],
        ]);
});

test('authenticated user can update their profile', function () {
    $token = $this->user->createToken('test-device')->plainTextToken;

    $response = $this->withToken($token)
        ->putJson('/api/v1/auth/user', [
            'name' => 'Updated Name',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'name' => 'Updated Name',
    ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $response = $this->getJson('/api/v1/auth/user');

    $response->assertUnauthorized();
});

test('invalid token cannot access protected routes', function () {
    $response = $this->withToken('invalid-token')
        ->getJson('/api/v1/auth/user');

    $response->assertUnauthorized();
});
