<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Team;
use App\Models\Transaction;
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
        $user = User::whereEmail('test@example.com')->first();

        if (! $user) {
            // Create main test user
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // // Create test team
        $team = Team::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Team',
            'description' => 'Test Team Description',
        ]);

        $user->teams()->attach($team);
        $user->switchTeam($team);

        // // Create additional users for the team
        // $additionalUsers = User::factory(3)->create();
        // foreach ($additionalUsers as $additionalUser) {
        //     $additionalUser->teams()->attach($team);
        // }

        // Create categories with predefined data
        $categoriesData = [
            ['name' => 'Utilities', 'icon' => '⚡', 'description' => 'Electric, water, gas, and other utilities', 'color' => '#f59e0b'],
            ['name' => 'Internet & Phone', 'icon' => '📱', 'description' => 'Internet, mobile, and phone services', 'color' => '#3b82f6'],
            ['name' => 'Subscriptions', 'icon' => '📺', 'description' => 'Streaming services, magazines, and other subscriptions', 'color' => '#8b5cf6'],
            ['name' => 'Insurance', 'icon' => '🛡️', 'description' => 'Health, auto, home, and life insurance', 'color' => '#10b981'],
            ['name' => 'Rent & Mortgage', 'icon' => '🏠', 'description' => 'Housing payments', 'color' => '#ef4444'],
            ['name' => 'Transportation', 'icon' => '🚗', 'description' => 'Car payments, gas, public transit', 'color' => '#06b6d4'],
            ['name' => 'Healthcare', 'icon' => '⚕️', 'description' => 'Medical bills and prescriptions', 'color' => '#ec4899'],
            ['name' => 'Entertainment', 'icon' => '🎮', 'description' => 'Gaming, movies, and entertainment', 'color' => '#f97316'],
        ];

        $categories = collect($categoriesData)->map(function ($categoryData) use ($team, $user) {
            return Category::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'icon' => $categoryData['icon'],
                'color' => $categoryData['color'],
            ]);
        });

        // Create bills for each category
        $allBills = collect();
        foreach ($categories as $category) {
            // Create 8-12 bills per category
            $billCount = rand(8, 12);
            $bills = Bill::factory($billCount)->create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'category_id' => $category->id,
            ]);
            $allBills = $allBills->merge($bills);
        }

        // Create some recurring bills
        Bill::factory(10)->recurring('monthly')->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'category_id' => $categories->random()->id,
        ]);

        Bill::factory(5)->recurring('yearly')->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'category_id' => $categories->random()->id,
        ]);

        // Create some bills with trial periods
        Bill::factory(8)->withTrial()->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'category_id' => $categories->random()->id,
        ]);

        // Refresh bills collection
        $allBills = Bill::where('team_id', $team->id)->get();

        // Create transactions for paid bills
        $paidBills = $allBills->where('status', 'paid');
        foreach ($paidBills as $bill) {
            // Each paid bill gets 1-3 transactions
            $transactionCount = rand(1, 3);
            Transaction::factory($transactionCount)->create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'bill_id' => $bill->id,
                'amount' => $bill->amount / $transactionCount,
            ]);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Created:');
        $this->command->info("- {$categories->count()} categories");
        $this->command->info("- {$allBills->count()} bills");
    }
}
