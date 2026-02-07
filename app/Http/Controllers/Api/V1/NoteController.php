<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Http\Resources\Api\V1\NoteResource;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Http\Request;

final class NoteController extends Controller
{
    public function __construct(
        private readonly NoteService $noteService,
    ) {}

    /**
     * Display a listing of the notes.
     */
    public function index(Request $request)
    {
        return NoteResource::collection($this->noteService->getNotes());
    }

    /**
     * Store a newly created note.
     */
    public function store(StoreNoteRequest $request)
    {
        $note = $this->noteService->createNote(
            $request->validated(),
            $request->user()->id,
            $request->user()->active_team_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Note created successfully',
            'data' => new NoteResource($note->load(['user', 'team', 'bills', 'transactions'])),
        ], 201);
    }

    /**
     * Display the specified note.
     */
    public function show(Note $note)
    {
        return response()->json([
            'success' => true,
            'data' => new NoteResource(
                $note->load(['user', 'team', 'bills', 'transactions'])
            ),
        ]);
    }

    /**
     * Update the specified note.
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $note = $this->noteService->updateNote(
            $note,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully',
            'data' => new NoteResource($note->load(['user', 'team', 'bills', 'transactions'])),
        ]);
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Note $note)
    {
        $this->noteService->deleteNote($note);

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }
}
