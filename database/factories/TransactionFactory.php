<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
final class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentMethods = [
            'Credit Card',
            'Debit Card',
            'Bank Transfer',
            'PayPal',
            'Cash',
            'Check',
            'Apple Pay',
            'Google Pay',
            'Venmo',
            'Zelle',
        ];

        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'bill_id' => Bill::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'payment_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'payment_method' => fake()->randomElement($paymentMethods),
            'attachment' => null,
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }
}
