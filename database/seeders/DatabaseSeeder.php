<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $team = Team::create([
            'user_id' => $user->id,
            'name' => 'Test Team',
            'description' => 'Test Team Description',
            'icon' => '/logo.svg',
            'status' => 'active',
        ]);

        $user->teams()->attach($team);

        $user->switchTeam($team);
    }
}
