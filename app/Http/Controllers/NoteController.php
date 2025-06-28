<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Note;
use App\Models\Scopes\TeamScope;
use Illuminate\Http\Request;

class NoteController extends Controller
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
        )->with('related')->get();

        return inertia('Notes/Index', [
            'bills' => Bill::all(),
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
            'related' => ['array', 'nullable'],
        ]);

        $note = Note::create($data);

        if ($data['related']) {
            $note->bills()->sync($data['related']);
        }

        return redirect()->route('notes.index')->with('success', 'Note created successfully.');
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
            'related' => ['array', 'nullable'],
        ]);

        $note->update($data);

        if ($data['related']) {
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
