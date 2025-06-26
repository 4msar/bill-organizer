<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    const ValidationRules = [
        'name' => ['required', 'string', 'max:100'],
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
                $data['icon'] = $request->file('icon')->store('teams');
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
    public function update(Request $request, Team $team)
    {
        $data = $request->validate(self::ValidationRules);

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
}
