<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/notifications');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the notifications page', function () {
    $user = User::factory()->create();
    $team = Team::create(['name' => 'Test Team', 'user_id' => $user->id]);
    $user->teams()->attach($team);
    $user->switchTeam($team);

    $this->actingAs($user);

    $billArray = [
        'bill_id' => 1,
        'team_id' => $team->id,
        'bill_slug' => 'sample-bill-slug',
        'title' => 'Sample Bill',
        'due_date' => now()->addDays(7)->toDateString(),
        'amount' => 1000,
    ];

    // Generate some notifications for the user
    DB::table('notifications')->insert([
        [
            'id' => '1',
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => json_encode($billArray),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'id' => '2',
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => json_encode($billArray),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this->get('/notifications');
    $response->assertStatus(200);
});
