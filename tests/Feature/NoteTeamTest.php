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

it('can create a personal note with user_id set', function () {
    $response = $this->post(route('notes.store'), [
        'title' => 'Personal Note',
        'content' => 'This is a personal note',
        'is_pinned' => false,
        'is_team_note' => false,
    ]);

    $response->assertRedirect(route('notes.index'));

    $note = Note::where('title', 'Personal Note')->first();
    expect($note)->not->toBeNull();
    expect($note->user_id)->toBe($this->user->id);
    expect($note->team_id)->toBe($this->team->id);
});

it('can create a team note with user_id as null', function () {
    $response = $this->post(route('notes.store'), [
        'title' => 'Team Note',
        'content' => 'This is a team note',
        'is_pinned' => false,
        'is_team_note' => true,
    ]);

    $response->assertRedirect(route('notes.index'));

    $note = Note::where('title', 'Team Note')->first();
    expect($note)->not->toBeNull();
    expect($note->user_id)->toBeNull();
    expect($note->team_id)->toBe($this->team->id);
});

it('can update a personal note to team note', function () {
    $note = Note::create([
        'title' => 'Original Note',
        'content' => 'Original content',
        'is_pinned' => false,
        'user_id' => $this->user->id,
        'team_id' => $this->team->id,
    ]);

    $response = $this->put(route('notes.update', $note), [
        'title' => 'Updated Note',
        'content' => 'Updated content',
        'is_team_note' => true,
    ]);

    $response->assertRedirect(route('notes.index'));

    $note->refresh();
    expect($note->user_id)->toBeNull();
    expect($note->title)->toBe('Updated Note');
});

it('can update a team note to personal note', function () {
    $note = Note::create([
        'title' => 'Team Note',
        'content' => 'Team content',
        'is_pinned' => false,
        'user_id' => null,
        'team_id' => $this->team->id,
    ]);

    $response = $this->put(route('notes.update', $note), [
        'title' => 'Personal Note',
        'content' => 'Personal content',
        'is_team_note' => false,
    ]);

    $response->assertRedirect(route('notes.index'));

    $note->refresh();
    expect($note->user_id)->toBe($this->user->id);
    expect($note->title)->toBe('Personal Note');
});
