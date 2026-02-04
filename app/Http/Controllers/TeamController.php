<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Mail\TeamInvitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

final class TeamController extends Controller
{
    const ValidationRules = [
        'name' => ['required', 'string', 'max:100'],
        'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:teams,slug'],
        'description' => ['string', 'max:255'],
        'icon' => ['image', 'nullable', 'max:512'],
        'currency' => ['string', 'required'],
        'currency_symbol' => ['string', 'required'],
    ];

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
    public function switch(Request $request, Team $team)
    {
        $request->user()->switchTeam($team);

        $refererUrlPath = parse_url(url()->previous(), PHP_URL_PATH);
        if (
            // If it's bill details page
            preg_match('/^\/bills\/[0-9]+$/', $refererUrlPath)
        ) {
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
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validate(self::ValidationRules);

            if ($request->hasFile('icon')) {
                $data['icon'] = $request->file('icon')->storePublicly('teams');
            }

            $team = $request->user()->teams()->create($data + [
                'user_id' => $request->user()->id,
                'status' => Status::Active,
            ]);

            $request->user()->teams()->attach($team);

            $request->user()->switchTeam($team);

            return to_route('dashboard')->with('success', 'Team created successfully.');
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        /** @var Team $team */
        $team = $request->user()->activeTeam;

        $rules = array_merge(self::ValidationRules, [
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:teams,slug,' . $team->id],
        ]);

        $data = $request->validate($rules);

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('teams');
        } else {
            unset($data['icon']);
        }

        $request->user()->activeTeam()->update($data);

        return back()->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $user->activeTeam()->delete();

        if ($user->teams->count() > 1) {
            $user->switchTeam($user->teams()->first());

            return to_route('dashboard');
        }

        return to_route('team.create');
    }

    /**
     * Show the form for joining a team.
     */
    public function join(string $teamId)
    {
        $team = Team::query()->withoutGlobalScope('user')->findOrFail($teamId);

        $mail = request()->query('email');
        if ($mail && $user = User::where('email', $mail)->first()) {
            // If the user already exists, switch to invited team
            $user->switchTeam($team);

            return to_route('dashboard')->with('success', 'Switched to your team successfully.');
        }

        // If the user does not exist, store the email in the session
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
    public function addMember(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = $request->user();
        $team = $user->activeTeam;

        // Check if the user already exists
        $member = User::where('email', $request->email)->first();

        if ($member) {
            // If the user exists, invite them to the team
            $team->users()->attach($member);

            return back()->with('success', 'Member added successfully.');
        }

        try {
            Mail::to($request->email)->send(new TeamInvitation($team, $user, $request->email));
        } catch (\Throwable $th) {
            // throw $th;
        }

        return back()->with('success', 'Invitation sent successfully.');
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Request $request, User $user)
    {
        $team = $request->user()->activeTeam;

        // Check if the user is the owner of the team
        if ($user->id === $team->user_id) {
            return back()->withErrors(['error' => 'Cannot remove the team owner.']);
        }

        // Remove the member from the team
        $team->users()->detach($user);

        return back()->with('success', 'Member removed successfully.');
    }

    public function removeLogo()
    {
        /** @var Team $team */
        $team = request()->user()->activeTeam;

        if (
            $team->icon &&
            ! str($team->icon)->startsWith('http') &&
            Storage::exists($team->icon)
        ) {
            Storage::delete($team->icon);
        }

        $team->icon = null;
        $team->save();

        return back()->with('success', 'Team logo removed successfully.');
    }
}
