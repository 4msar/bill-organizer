<?php

namespace App\Actions\Notes;

use App\Contracts\Action;
use App\Models\Note;

final class CreateNoteAction implements Action
{
    /**
     * Create a new note
     *
     * @param  array  $data
     * @param  int  $userId
     * @param  int  $teamId
     */
    public function execute(mixed ...$params): Note
    {
        $data = $params[0];
        $userId = $params[1];
        $teamId = $params[2];

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
}
