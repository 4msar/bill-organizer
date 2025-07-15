<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
            'status' => fake()->randomElement(['unpaid', 'paid']),
            'is_recurring' => fake()->boolean(20),
            'recurrence_period' => fake()->optional(20)->randomElement(['weekly', 'monthly', 'yearly']),
            'payment_url' => fake()->optional(30)->url(),
            'tags' => fake()->optional(40)->randomElements(['utility', 'rent', 'subscription', 'insurance'], 2),
            'has_trial' => false,
            'trial_start_date' => null,
            'trial_end_date' => null,
            'trial_status' => null,
        ];
    }

    /**
     * Indicate that the bill has a trial period.
     */
    public function withTrial(int $trialDays = 14): static
    {
        return $this->state(fn (array $attributes) => [
            'has_trial' => true,
            'trial_start_date' => now(),
            'trial_end_date' => now()->addDays($trialDays),
            'trial_status' => 'active',
        ]);
    }

    /**
     * Indicate that the bill has an expired trial.
     */
    public function withExpiredTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_trial' => true,
            'trial_start_date' => now()->subDays(30),
            'trial_end_date' => now()->subDays(5),
            'trial_status' => 'expired',
        ]);
    }

    /**
     * Indicate that the bill trial was converted.
     */
    public function withConvertedTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_trial' => true,
            'trial_start_date' => now()->subDays(20),
            'trial_end_date' => now()->subDays(5),
            'trial_status' => 'converted',
        ]);
    }

    /**
     * Indicate that the bill is unpaid.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unpaid',
        ]);
    }

    /**
     * Indicate that the bill is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }
}