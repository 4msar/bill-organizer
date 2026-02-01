<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Note;
use App\Models\Scopes\TeamScope;
use Illuminate\Http\Request;

final class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Shows all notes for the current team by default.
     * If you want to show all teams notes, you can pass 'all' as a query parameter.
     * available query parameters: all, current
     */
    public function index()
    {
        $notes = Note::when(
            request('team', 'current') === 'all',
            function ($query) {
                $query->withoutGlobalScope(TeamScope::class);
                $query->with('team');
            }
        )
            ->with('related')
            ->latest('updated_at')
            ->get();

        return inertia('Notes/Index', [
            'bills' => Bill::all(['id', 'title']),
            'notes' => $notes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_pinned' => 'boolean',
            'is_team_note' => 'boolean',
            'related' => ['array', 'nullable'],
        ]);

        // Set team_id from current user's active team
        $data['team_id'] = $request->user()->active_team_id;

        // Handle team note logic: if is_team_note is true, set user_id to null
        if (isset($data['is_team_note']) && $data['is_team_note']) {
            $data['user_id'] = null;
        } else {
            // For personal notes, set user_id to current user
            $data['user_id'] = $request->user()->id;
        }

        // Remove is_team_note from data as it's not a database field
        unset($data['is_team_note']);

        $note = Note::create($data);

        if (isset($data['related']) && $data['related']) {
            $note->bills()->sync($data['related']);
        }

        return redirect()
            ->route('notes.index')
            ->with('noteId', $note->id)
            ->with('success', 'Note created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        $data = $request->validate([
            'title' => ['required', 'sometimes', 'string', 'max:255'],
            'content' => ['required', 'sometimes', 'string'],
            'is_pinned' => ['boolean', 'sometimes'],
            'is_team_note' => ['boolean', 'sometimes'],
            'related' => ['array', 'nullable'],
        ]);

        // Handle team note logic: if is_team_note is true, set user_id to null
        if (isset($data['is_team_note'])) {
            if ($data['is_team_note']) {
                $data['user_id'] = null;
            } else {
                $data['user_id'] = $request->user()->id;
            }
        }

        // Remove is_team_note from data as it's not a database field
        unset($data['is_team_note']);

        $note->update($data);

        if (isset($data['related'])) {
            $note->bills()->sync($data['related']);
        }

        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }
}
