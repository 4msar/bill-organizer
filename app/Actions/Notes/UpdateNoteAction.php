<?php

namespace App\Actions\Notes;

use App\Contracts\Action;
use App\Models\Note;

final class UpdateNoteAction implements Action
{
    /**
     * Update a note
     *
     * @param  Note  $note
     * @param  array  $data
     * @param  int|null  $userId
     */
    public function execute(mixed ...$params): Note
    {
        $note = $params[0];
        $data = $params[1];
        $userId = $params[2] ?? null;

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
}
