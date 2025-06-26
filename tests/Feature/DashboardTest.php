<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $team = Team::create(['name' => 'Test Team', 'user_id' => $user->id]);
    $user->teams()->attach($team);
    $user->switchTeam($team);
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
