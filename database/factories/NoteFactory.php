<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
final class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Payment Reminder',
            'Important Bill Note',
            'Due Date Changed',
            'Payment Confirmation',
            'Bill Inquiry',
            'Service Update',
            'Rate Change Notice',
            'Account Information',
            'Billing Question',
            'Payment Plan',
        ];

        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'title' => fake()->randomElement($titles),
            'content' => fake()->paragraph(3),
            'is_pinned' => fake()->boolean(20), // 20% chance of being pinned
        ];
    }

    /**
     * Indicate that the note is pinned.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }
}
