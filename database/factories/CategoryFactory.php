<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
final class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            ['name' => 'Utilities', 'icon' => '⚡', 'description' => 'Electric, water, gas, and other utilities', 'color' => '#f59e0b'],
            ['name' => 'Internet & Phone', 'icon' => '📱', 'description' => 'Internet, mobile, and phone services', 'color' => '#3b82f6'],
            ['name' => 'Subscriptions', 'icon' => '📺', 'description' => 'Streaming services, magazines, and other subscriptions', 'color' => '#8b5cf6'],
            ['name' => 'Insurance', 'icon' => '🛡️', 'description' => 'Health, auto, home, and life insurance', 'color' => '#10b981'],
            ['name' => 'Rent & Mortgage', 'icon' => '🏠', 'description' => 'Housing payments', 'color' => '#ef4444'],
            ['name' => 'Transportation', 'icon' => '🚗', 'description' => 'Car payments, gas, public transit', 'color' => '#06b6d4'],
            ['name' => 'Healthcare', 'icon' => '⚕️', 'description' => 'Medical bills and prescriptions', 'color' => '#ec4899'],
            ['name' => 'Education', 'icon' => '📚', 'description' => 'Tuition, courses, and educational materials', 'color' => '#6366f1'],
            ['name' => 'Entertainment', 'icon' => '🎮', 'description' => 'Gaming, movies, and entertainment', 'color' => '#f97316'],
            ['name' => 'Groceries', 'icon' => '🛒', 'description' => 'Food and household items', 'color' => '#14b8a6'],
        ];

        $category = fake()->randomElement($categories);

        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'name' => $category['name'],
            'description' => $category['description'],
            'icon' => $category['icon'],
            'color' => $category['color'],
        ];
    }
}
