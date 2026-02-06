<?php

use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->withTeam()->create();
    $this->user->refresh();
    $this->team = $this->user->teams()->first();
    if ($this->team) {
        $this->user->switchTeam($this->team);
        $this->user->refresh();
    }
    $this->token = $this->user->createToken('test')->plainTextToken;
});

test('can list teams', function () {
    Team::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ])->each(function ($team) {
        $this->user->teams()->attach($team);
    });

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/teams');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'status',
                ],
            ],
            'links',
            'meta',
        ]);
});

test('can filter teams by status', function () {
    Team::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'active',
        'name' => 'Active Team',
    ]);

    Team::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'inactive',
        'name' => 'Inactive Team',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/teams?status=active');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2); // including the initial team from beforeEach
});

test('can filter teams by user_id', function () {
    $otherUser = User::factory()->create();

    Team::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'My Team',
    ]);

    Team::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Other User Team',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/teams?user_id='.$this->user->id);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2); // including the initial team from beforeEach
});

test('can search teams', function () {
    Team::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Development Team',
        'description' => 'Team for developers',
    ]);

    Team::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Marketing Team',
        'description' => 'Team for marketing',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/teams?search=Development');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toContain('Development');
});

test('can search teams by description', function () {
    Team::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Alpha Team',
        'description' => 'Engineering projects',
    ]);

    Team::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Beta Team',
        'description' => 'Marketing campaigns',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/teams?search=Engineering');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Alpha Team');
});

test('can create team', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/teams', [
            'name' => 'New Team',
            'slug' => 'new-team',
            'description' => 'Test team',
            'currency' => 'USD',
            'currency_symbol' => '$',
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'slug', 'description'],
        ]);

    $this->assertDatabaseHas('teams', [
        'name' => 'New Team',
        'slug' => 'new-team',
        'user_id' => $this->user->id,
        'status' => 'active',
    ]);

    $team = Team::where('slug', 'new-team')->first();
    expect($this->user->teams->contains($team))->toBeTrue();
});

test('can show team', function () {
    $team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/teams/{$team->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $team->id,
                'name' => $team->name,
            ],
        ]);
});

test('can update team', function () {
    $team = Team::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Old Name',
        'slug' => 'old-slug',
    ]);

    $response = $this->withToken($this->token)
        ->putJson("/api/v1/teams/{$team->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Team updated successfully',
        ]);

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);
});

test('can delete team', function () {
    $team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/teams/{$team->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Team deleted successfully',
        ]);

    $this->assertSoftDeleted('teams', ['id' => $team->id]);
});

test('can add member to team', function () {
    $team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $newMember = User::factory()->create();

    $response = $this->withToken($this->token)
        ->postJson("/api/v1/teams/{$team->id}/members", [
            'user_id' => $newMember->id,
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Member added successfully',
        ]);

    expect($team->users->contains($newMember))->toBeTrue();
});

test('cannot add existing member to team', function () {
    $team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $member = User::factory()->create();
    $team->users()->attach($member);

    $response = $this->withToken($this->token)
        ->postJson("/api/v1/teams/{$team->id}/members", [
            'user_id' => $member->id,
        ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'User is already a member of this team',
        ]);
});

test('can remove member from team', function () {
    $team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $member = User::factory()->create();
    $team->users()->attach($member);

    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/teams/{$team->id}/members/{$member->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Member removed successfully',
        ]);

    expect($team->fresh()->users->contains($member))->toBeFalse();
});

test('cannot remove team owner from team', function () {
    $team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/teams/{$team->id}/members/{$this->user->id}");

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Cannot remove the team owner',
        ]);
});

test('only team owner can remove members', function () {
    $team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $member1 = User::factory()->create();
    $member2 = User::factory()->create();
    $team->users()->attach([$member1->id, $member2->id]);

    $member1Token = $member1->createToken('test')->plainTextToken;

    $response = $this->withToken($member1Token)
        ->deleteJson("/api/v1/teams/{$team->id}/members/{$member2->id}");

    // This depends on authorization logic in the controller
    // Assuming there's a policy or gate that prevents non-owners from removing members
    // If not implemented, this test documents expected behavior
    $response->assertStatus(403);
});

test('can switch to team', function () {
    $team1 = $this->user->teams->first();
    $team2 = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->user->teams()->attach($team2);

    $response = $this->withToken($this->token)
        ->postJson("/api/v1/teams/{$team2->id}/switch");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Team switched successfully',
        ]);

    $this->user->refresh();
    expect($this->team->id)->toBe($team2->id);
});

test('cannot switch to team user does not belong to', function () {
    $otherUserTeam = Team::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $response = $this->withToken($this->token)
        ->postJson("/api/v1/teams/{$otherUserTeam->id}/switch");

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'You do not belong to this team',
        ]);
});

test('cannot access teams without authentication', function () {
    $response = $this->getJson('/api/v1/teams');

    $response->assertUnauthorized();
});

test('cannot access teams without team', function () {
    $userWithoutTeam = User::factory()->create();
    $token = $userWithoutTeam->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/teams');

    $response->assertStatus(403);
});

test('validates required fields when creating team', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/teams', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'slug', 'currency', 'currency_symbol']);
});

test('validates unique slug when creating team', function () {
    Team::factory()->create([
        'user_id' => $this->user->id,
        'slug' => 'existing-slug',
    ]);

    $response = $this->withToken($this->token)
        ->postJson('/api/v1/teams', [
            'name' => 'New Team',
            'slug' => 'existing-slug',
            'currency' => 'USD',
            'currency_symbol' => '$',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});

test('can sort teams', function () {
    Team::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Zebra Team',
    ]);

    Team::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Alpha Team',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/teams?sort_by=name&sort_direction=asc');

    $response->assertOk();
    expect($response->json('data.0.name'))->toBe('Alpha Team');
});
