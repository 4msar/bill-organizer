<?php

namespace App\Actions\Notes;

use App\Contracts\Action;
use App\Models\Note;

final class DeleteNoteAction implements Action
{
    /**
     * Delete a note
     *
     * @param  Note  $note
     */
    public function execute(mixed ...$params): void
    {
        $note = $params[0];

        $note->delete();
    }
}
