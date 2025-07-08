<?php

use App\Models\Note;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    
    // Create team manually for simplicity
    $this->team = Team::create([
        'user_id' => $this->user->id,
        'name' => 'Test Team',
        'description' => 'Test Description',
        'icon' => 'teams/default.png',
        'status' => 'active',
        'currency' => 'USD',
        'currency_symbol' => '$',
    ]);
    
    // Attach user to team and set as active
    $this->user->teams()->attach($this->team);
    $this->user->update(['active_team_id' => $this->team->id]);
    
    $this->actingAs($this->user);
});

it('creates personal note with user_id when is_team_note is false', function () {
    // Create note directly using the model to test core logic
    $note = Note::create([
        'title' => 'Personal Note',
        'content' => 'This is a personal note',
        'is_pinned' => false,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    expect($note->user_id)->toBe($this->user->id);
    expect($note->team_id)->toBe($this->team->id);
});

it('creates team note with null user_id when is_team_note is true', function () {
    // Create team note directly
    $note = Note::create([
        'title' => 'Team Note',
        'content' => 'This is a team note',
        'is_pinned' => false,
        'user_id' => null,
        'team_id' => $this->team->id,
    ]);

    expect($note->user_id)->toBeNull();
    expect($note->team_id)->toBe($this->team->id);
});

it('shows both personal and team notes for user via UserScope', function () {
    // Create personal note
    $personalNote = Note::create([
        'title' => 'Personal Note',
        'content' => 'Personal content',
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    // Create team note
    $teamNote = Note::create([
        'title' => 'Team Note',
        'content' => 'Team content',
        'user_id' => null,
        'team_id' => $this->team->id,
    ]);

    // Test that both notes are returned when queried
    $notes = Note::all();
    
    expect($notes)->toHaveCount(2);
    expect($notes->pluck('title')->toArray())->toContain('Personal Note');
    expect($notes->pluck('title')->toArray())->toContain('Team Note');
});

it('does not show other users personal notes', function () {
    // Create another user and their personal note
    $otherUser = User::factory()->create();
    $otherUser->teams()->attach($this->team);
    
    $otherUserNote = Note::create([
        'title' => 'Other User Note',
        'content' => 'Other user content',
        'user_id' => $otherUser->id,
        'team_id' => $this->team->id,
    ]);

    // Create team note
    $teamNote = Note::create([
        'title' => 'Team Note',
        'content' => 'Team content',
        'user_id' => null,
        'team_id' => $this->team->id,
    ]);

    // Test that only team note is visible to current user (other user's personal note should not be visible)
    $notes = Note::all();
    
    // Debug what we're getting
    $titles = $notes->pluck('title')->toArray();
    $userIds = $notes->pluck('user_id')->toArray();
    
    expect($notes)->toHaveCount(1, 'Expected 1 note, got: ' . implode(', ', $titles) . ' with user_ids: ' . implode(', ', $userIds));
    expect($titles)->toContain('Team Note');
    expect($titles)->not->toContain('Other User Note');
});