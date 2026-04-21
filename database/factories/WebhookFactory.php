<?php

namespace Database\Factories;

use App\Enums\WebhookEvent;
use App\Models\Team;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Webhook>
 */
final class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

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
            'name' => fake()->words(3, true),
            'url' => fake()->url(),
            'secret' => Str::random(40),
            'events' => [WebhookEvent::BillingCreated->value],
            'is_active' => true,
        ];
    }

    /**
     * Create an inactive webhook.
     */
    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
