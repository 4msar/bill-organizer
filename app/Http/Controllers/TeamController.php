<?php

namespace App\Http\Controllers;

use App\Http\Requests\Team\AddTeamMemberRequest;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamService;
use Illuminate\Http\Request;

final class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $user->activeTeam->load(['users', 'owner']);

        if ($user->activeTeam->user_id !== $user->id) {
            abort(403, 'You are not the owner of this team, only owner can update team.');
        }

        return inertia('Teams/Edit');
    }

    /**
     * Switch the current team.
     */
    public function switch(Request $request, Team $team, TeamService $teamService)
    {
        $teamService->switchTeam($request->user(), $team);

        $refererUrlPath = parse_url(url()->previous(), PHP_URL_PATH);
        if (preg_match('/^\/bills\/[0-9]+$/', $refererUrlPath)) {
            return to_route('bills.index')->with('success', 'Team switched successfully.');
        }

        return back()->with('success', 'Team switched successfully.');
    }

    /**
     * Create a new team.
     */
    public function create()
    {
        return inertia('Teams/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request, TeamService $teamService)
    {
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->storePublicly('teams');
        }

        $teamService->createTeam($request->user(), $data);

        return to_route('dashboard')->with('success', 'Team created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, TeamService $teamService)
    {
        $team = $request->user()->activeTeam;
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('teams');
        } else {
            unset($data['icon']);
        }

        $teamService->updateTeam($team, $data);

        return back()->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, TeamService $teamService)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $teamService->deleteTeam($user->activeTeam);

        if ($user->teams->count() > 1) {
            $teamService->switchTeam($user, $user->teams()->first());

            return to_route('dashboard');
        }

        return to_route('team.create');
    }

    /**
     * Show the form for joining a team.
     */
    public function join(string $teamId, TeamService $teamService)
    {
        $team = Team::query()->withoutGlobalScope('user')->findOrFail($teamId);

        $mail = request()->query('email');
        if ($mail && $user = User::where('email', $mail)->first()) {
            $teamService->switchTeam($user, $team);

            return to_route('dashboard')->with('success', 'Switched to your team successfully.');
        }

        session(['join_team' => [
            'email' => $mail,
            'team' => $teamId,
        ]]);

        return inertia('auth/Register', [
            'email' => $mail,
        ]);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(AddTeamMemberRequest $request, TeamService $teamService)
    {
        $validated = $request->validated();
        $user = $request->user();
        $team = $user->activeTeam;

        $teamService->inviteMember($team, $user, $validated['email']);

        return back()->with('success', 'Member added successfully.');
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Request $request, User $user, TeamService $teamService)
    {
        $team = $request->user()->activeTeam;

        try {
            $teamService->removeMember($team, $user);

            return back()->with('success', 'Member removed successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function removeLogo(TeamService $teamService)
    {
        $team = request()->user()->activeTeam;

        $teamService->removeLogo($team);

        return back()->with('success', 'Team logo removed successfully.');
    }
}
