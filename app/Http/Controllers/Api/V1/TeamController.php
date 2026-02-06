<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\AddTeamMemberRequest;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\Api\V1\TeamResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index(Request $request)
    {
        $query = Team::with(['owner', 'users'])
            ->when($request->search, function ($q, $search) {
                if (str_contains($search, ':')) {
                    [$column, $value] = explode(':', $search);
                    if ($column && $value && in_fillable($column, Team::class)) {
                        return $q->where($column, 'like', '%'.$value.'%');
                    }
                }

                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->user_id, function ($q, $userId) {
                $q->where('user_id', $userId);
            });

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (in_fillable($sortBy, Team::class)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100);
        $teams = $query->paginate($perPage);

        return TeamResource::collection($teams);
    }

    /**
     * Store a newly created team.
     */
    public function store(StoreTeamRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();

            $validated['user_id'] = $request->user()->id;
            $validated['status'] = Status::Active;

            $team = Team::create($validated);

            // Attach the creator to the team
            $request->user()->teams()->attach($team);

            return response()->json([
                'success' => true,
                'message' => 'Team created successfully',
                'data' => new TeamResource($team->load(['owner', 'users'])),
            ], 201);
        });
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        return response()->json([
            'success' => true,
            'data' => new TeamResource($team->load(['owner', 'users', 'bills', 'categories', 'transactions', 'notes'])),
        ]);
    }

    /**
     * Update the specified team.
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $validated = $request->validated();

        $team->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully',
            'data' => new TeamResource($team->fresh()->load(['owner', 'users'])),
        ]);
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully',
        ]);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(AddTeamMemberRequest $request, Team $team)
    {
        $validated = $request->validated();

        $user = User::findOrFail($validated['user_id'] ?? $validated['email']);

        // Check if user is already a member
        if ($team->users()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this team',
            ], 422);
        }

        $team->users()->attach($user);

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully',
            'data' => new TeamResource($team->fresh()->load(['owner', 'users'])),
        ]);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Request $request, Team $team, User $user)
    {
        // Check if the user is the owner
        if ($user->id === $team->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the team owner',
            ], 422);
        }

        // Check if the current user is the team owner
        if ($request->user()->id !== $team->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the team owner can remove members',
            ], 403);
        }

        $team->users()->detach($user);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully',
            'data' => new TeamResource($team->fresh()->load(['owner', 'users'])),
        ]);
    }

    /**
     * Switch the active team for the authenticated user.
     */
    public function switch(Request $request, Team $team)
    {
        $user = $request->user();

        // Check if user belongs to the team
        if (! $user->hasTeam($team->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not belong to this team',
            ], 403);
        }

        $user->switchTeam($team);

        return response()->json([
            'success' => true,
            'message' => 'Team switched successfully',
            'data' => new TeamResource($team->load(['owner', 'users'])),
        ]);
    }
}
