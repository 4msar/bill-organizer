<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\AddTeamMemberRequest;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Resources\Api\V1\TeamResource;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamService;
use Illuminate\Http\Request;

final class TeamController extends Controller
{
    function __construct(
        protected TeamService $teamService
    ) {}

    /**
     * Display a listing of the teams.
     */
    public function index(Request $request)
    {
        $teams = $this->teamService->getTeams($request);

        return TeamResource::collection($teams);
    }

    /**
     * Store a newly created team.
     */
    public function store(StoreTeamRequest $request)
    {
        $team = $this->teamService->createTeam($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully',
            'data' => new TeamResource($team->load(['owner', 'users'])),
        ], 201);
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
        $this->teamService->updateTeam($team, $request->validated());

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
        $this->teamService->deleteTeam($team);

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
        $email = $request->validated('email');
        $userId = $request->validated('user_id');
        $user = $request->user();
        $team = $user->activeTeam;

        if ($userId ?? false) {
            $email = User::find($userId)->email;
        }

        if ($this->teamService->isMember($team, $email)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this team',
            ], 422);
        }

        $this->teamService->inviteMember($team, $user, $email);

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
        // Check if the current user is the team owner
        if ($request->user()->id !== $team->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the team owner can remove members',
            ], 403);
        }

        try {
            $this->teamService->removeMember($team, $user);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully',
                'data' => new TeamResource($team->fresh()->load(['owner', 'users'])),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Switch the active team for the authenticated user.
     */
    public function switch(Request $request, Team $team, TeamService $teamService)
    {
        $user = $request->user();

        // Check if user belongs to the team
        if (! $user->hasTeam($team->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not belong to this team',
            ], 403);
        }

        $teamService->switchTeam($user, $team);

        return response()->json([
            'success' => true,
            'message' => 'Team switched successfully',
            'data' => new TeamResource($team->load(['owner', 'users'])),
        ]);
    }
}
