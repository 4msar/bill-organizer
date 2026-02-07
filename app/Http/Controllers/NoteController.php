<?php

namespace App\Http\Controllers;

use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\Bill;
use App\Models\Note;
use App\Models\Scopes\TeamScope;
use App\Services\NoteService;

final class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Shows all notes for the current team by default.
     * If you want to show all teams notes, you can pass 'all' as a query parameter.
     * available query parameters: all, current
     */
    public function index()
    {
        $notes = $this->noteService->getNotes();

        return inertia('Notes/Index', [
            'bills' => Bill::all(['id', 'title']),
            'notes' => $notes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request)
    {
        $note = $this->noteService->createNote(
            $request->validated(),
            $request->user()->id,
            $request->user()->active_team_id
        );

        return redirect()
            ->route('notes.index')
            ->with('noteId', $note->id)
            ->with('success', 'Note created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $this->noteService->updateNote(
            $note,
            $request->validated(),
            $request->user()->id
        );

        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $this->noteService->deleteNote($note);

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }
}
