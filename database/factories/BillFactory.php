<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
final class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isRecurring = fake()->boolean(70); // 70% chance of being recurring
        $hasTrial = fake()->boolean(30); // 30% chance of having a trial

        $billTitles = [
            'Electric Bill',
            'Water Bill',
            'Gas Bill',
            'Internet Service',
            'Mobile Phone',
            'Netflix Subscription',
            'Spotify Premium',
            'Amazon Prime',
            'Car Insurance',
            'Health Insurance',
            'Rent Payment',
            'Mortgage Payment',
            'Gym Membership',
            'Cloud Storage',
            'Adobe Creative Cloud',
            'Microsoft 365',
            'Hulu Subscription',
            'Disney+ Subscription',
            'HBO Max',
            'Cable TV',
        ];

        $tags = ['essential', 'entertainment', 'subscription', 'insurance', 'monthly', 'annual', 'utilities'];

        $dueDate = fake()->dateTimeBetween('-6 months', '+6 months');

        $definition = [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->randomElement($billTitles),
            'description' => fake()->optional(0.6)->sentence(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'due_date' => $dueDate,
            'status' => fake()->randomElement(['paid', 'unpaid']),
            'is_recurring' => $isRecurring,
            'recurrence_period' => $isRecurring ? fake()->randomElement(['weekly', 'monthly', 'yearly']) : null,
            'payment_url' => fake()->optional(0.4)->url(),
            'tags' => fake()->optional(0.5)->randomElements($tags, fake()->numberBetween(1, 3)),
            'has_trial' => false,
            'trial_start_date' => null,
            'trial_end_date' => null,
        ];

        // Add trial dates if has_trial is true
        if ($hasTrial && $definition['status'] === 'unpaid') {
            $trialStart = fake()->dateTimeBetween('-1 month', '+1 week');
            $definition['has_trial'] = true;
            $definition['trial_start_date'] = $trialStart;
            $definition['trial_end_date'] = fake()->dateTimeBetween($trialStart, '+1 month');
        }

        return $definition;
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
     * Indicate that the bill is recurring.
     */
    public function recurring(string $period = 'monthly'): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_period' => $period,
        ]);
    }

    /**
     * Indicate that the bill has a trial period.
     */
    public function withTrial(): static
    {
        return $this->state(function (array $attributes) {
            $trialStart = fake()->dateTimeBetween('-1 month', '+1 week');

            return [
                'has_trial' => true,
                'trial_start_date' => $trialStart,
                'trial_end_date' => fake()->dateTimeBetween($trialStart, '+1 month'),
            ];
        });
    }
}
