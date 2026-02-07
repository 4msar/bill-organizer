<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->whereHas('teams', function ($q) {
                $q->where('teams.id', active_team_id());
            })->get();

        return UserResource::collection($users);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => new UserResource($user->load([
                'activeTeam',
                'teams',
                'bills',
                'categories',
                'transactions',
                'notes'
            ])),
        ]);
    }
}
