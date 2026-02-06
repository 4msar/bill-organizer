<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Http\Resources\Api\V1\NoteResource;
use App\Models\Note;
use Illuminate\Http\Request;

final class NoteController extends Controller
{
    /**
     * Display a listing of the notes.
     */
    public function index(Request $request)
    {
        $query = Note::with(['user', 'team', 'bills', 'transactions'])
            ->when($request->search, function ($q, $search) {
                if (str_contains($search, ':')) {
                    [$column, $value] = explode(':', $search);
                    if ($column && $value && in_fillable($column, Note::class)) {
                        return $q->where($column, 'like', '%' . $value . '%');
                    }
                }

                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            })
            ->when($request->user_id, function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when($request->team_id, function ($q, $teamId) {
                $q->where('team_id', $teamId);
            })
            ->when($request->is_pinned !== null, function ($q) use ($request) {
                $q->where('is_pinned', $request->boolean('is_pinned'));
            })
            ->when($request->boolean('team_notes_only'), function ($q) {
                $q->whereNull('user_id');
            })
            ->when($request->boolean('personal_notes_only'), function ($q) {
                $q->whereNotNull('user_id');
            });

        // Sorting
        $sortBy = $request->input('sort_by', 'updated_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (in_fillable($sortBy, Note::class)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100);
        $notes = $query->paginate($perPage);

        return NoteResource::collection($notes);
    }

    /**
     * Store a newly created note.
     */
    public function store(StoreNoteRequest $request)
    {
        $validated = $request->validated();

        $validated['team_id'] = $request->user()->active_team_id;

        // Handle team note logic: if is_team_note is true, set user_id to null
        if (isset($validated['is_team_note']) && $validated['is_team_note']) {
            $validated['user_id'] = null;
        } else {
            $validated['user_id'] = $request->user()->id;
        }

        // Remove is_team_note from data as it's not a database field
        unset($validated['is_team_note']);

        $note = Note::create($validated);

        if (isset($validated['related']) && $validated['related']) {
            $note->bills()->sync($validated['related']);
        }

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
            'data' => new NoteResource($note->load(['user', 'team', 'bills', 'transactions'])),
        ]);
    }

    /**
     * Update the specified note.
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $validated = $request->validated();

        // Handle team note logic: if is_team_note is true, set user_id to null
        if (isset($validated['is_team_note'])) {
            if ($validated['is_team_note']) {
                $validated['user_id'] = null;
            } else {
                $validated['user_id'] = $request->user()->id;
            }
        }

        // Remove is_team_note from data as it's not a database field
        unset($validated['is_team_note']);

        $note->update($validated);

        if (isset($validated['related'])) {
            $note->bills()->sync($validated['related']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully',
            'data' => new NoteResource($note->fresh()->load(['user', 'team', 'bills', 'transactions'])),
        ]);
    }

    /**
     * Remove the specified note.
     */
    public function destroy(Note $note)
    {
        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }
}
