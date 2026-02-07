<?php

namespace App\Services;

use App\Models\Note;

final class NoteService
{
    /**
     * Create a new note
     */
    public function createNote(array $data, int $userId, int $teamId): Note
    {
        // Set team_id
        $data['team_id'] = $teamId;

        // Handle team note logic: if is_team_note is true, set user_id to null
        if (isset($data['is_team_note']) && $data['is_team_note']) {
            $data['user_id'] = null;
        } else {
            $data['user_id'] = $userId;
        }

        // Remove is_team_note from data as it's not a database field
        unset($data['is_team_note']);

        // Extract related bills if present
        $relatedBills = $data['related'] ?? null;
        unset($data['related']);

        $note = Note::create($data);

        // Sync related bills
        if ($relatedBills) {
            $note->bills()->sync($relatedBills);
        }

        return $note;
    }

    /**
     * Update a note
     */
    public function updateNote(Note $note, array $data, ?int $userId = null): Note
    {
        // Handle team note logic
        if (isset($data['is_team_note'])) {
            $data['user_id'] = $data['is_team_note'] ? null : ($userId ?? $note->user_id);
            unset($data['is_team_note']);
        }

        // Extract related bills if present
        $relatedBills = $data['related'] ?? null;
        unset($data['related']);

        $note->update($data);

        // Sync related bills
        if ($relatedBills !== null) {
            $note->bills()->sync($relatedBills);
        }

        return $note->fresh();
    }

    /**
     * Delete a note
     */
    public function deleteNote(Note $note): void
    {
        $note->delete();
    }
}
