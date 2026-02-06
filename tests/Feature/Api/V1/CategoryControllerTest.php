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

test('can list categories', function () {
    Category::factory()->count(5)->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/categories');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'icon',
                    'color',
                ],
            ],
            'links',
            'meta',
        ]);
});

test('can filter categories by user_id', function () {
    $otherUser = User::factory()->create();

    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'My Category',
    ]);

    Category::factory()->create([
        'user_id' => $otherUser->id,
        'team_id' => $this->team->id,
        'name' => 'Other Category',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/categories?user_id='.$this->user->id);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('My Category');
});

test('can filter categories by team_id', function () {
    $otherTeam = \App\Models\Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Team Category',
    ]);

    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $otherTeam->id,
        'name' => 'Other Team Category',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/categories?team_id='.$this->team->id);

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Team Category');
});

test('can search categories', function () {
    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Utilities',
        'description' => 'Electric and water bills',
    ]);

    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Entertainment',
        'description' => 'Movies and games',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/categories?search=Utilities');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toContain('Utilities');
});

test('can search categories by description', function () {
    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Utilities',
        'description' => 'Electric and water bills',
    ]);

    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Entertainment',
        'description' => 'Movies and games',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/categories?search=water');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Utilities');
});

test('can include bills count with categories', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Bill::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'category_id' => $category->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/categories?with_bills_count=true');

    $response->assertOk();
    expect($response->json('data.0'))->toHaveKey('bills_count');
    expect($response->json('data.0.bills_count'))->toBe(3);
});

test('can create category', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/categories', [
            'name' => 'New Category',
            'description' => 'Test category',
            'icon' => '📱',
            'color' => '#3b82f6',
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'description', 'icon', 'color'],
        ]);

    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
        'description' => 'Test category',
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);
});

test('can show category', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/categories/{$category->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ]);
});

test('can update category', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Old Name',
    ]);

    $response = $this->withToken($this->token)
        ->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Category updated successfully',
        ]);

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);
});

test('can delete category without bills', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('cannot delete category with bills', function () {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    Bill::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'category_id' => $category->id,
    ]);

    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('cannot access categories without authentication', function () {
    $response = $this->getJson('/api/v1/categories');

    $response->assertUnauthorized();
});

test('cannot access categories without team', function () {
    $userWithoutTeam = User::factory()->create();
    $token = $userWithoutTeam->createToken('test')->plainTextToken;

    $response = $this->withToken($token)
        ->getJson('/api/v1/categories');

    $response->assertStatus(403);
});

test('categories are team scoped', function () {
    $otherUser = User::factory()->withTeam()->create();
    $otherUserTeam = Team::withoutGlobalScopes()->find($otherUser->active_team_id);

    Category::factory()->create([
        'user_id' => $otherUser->id,
        'team_id' => $otherUserTeam->id,
        'name' => 'Other Team Category',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/categories');

    $response->assertOk();
    $categoryNames = collect($response->json('data'))->pluck('name');
    expect($categoryNames)->not->toContain('Other Team Category');
});

test('validates required fields when creating category', function () {
    $response = $this->withToken($this->token)
        ->postJson('/api/v1/categories', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('can sort categories', function () {
    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Zebra',
    ]);

    Category::factory()->create([
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
        'name' => 'Apple',
    ]);

    $response = $this->withToken($this->token)
        ->getJson('/api/v1/categories?sort_by=name&sort_direction=asc');

    $response->assertOk();
    expect($response->json('data.0.name'))->toBe('Apple');
    expect($response->json('data.1.name'))->toBe('Zebra');
});
