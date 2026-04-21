<?php

namespace Database\Factories;

use App\Enums\WebhookEvent;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebhookDelivery>
 */
final class WebhookDeliveryFactory extends Factory
{
    protected $model = WebhookDelivery::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'webhook_id' => Webhook::factory(),
            'event' => fake()->randomElement(WebhookEvent::values()),
            'payload' => ['id' => fake()->randomNumber()],
            'status' => fake()->randomElement(['pending', 'success', 'failed']),
            'response_status' => fake()->randomElement([200, 201, 400, 500, null]),
            'response_body' => fake()->optional()->sentence(),
            'attempts' => fake()->numberBetween(1, 5),
            'delivered_at' => null,
        ];
    }
}
