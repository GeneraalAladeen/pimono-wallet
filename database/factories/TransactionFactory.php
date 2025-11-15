<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomDigit(3, true);
        $commission = $amount * 0.015;

        return [
            'receiver_id' => User::factory(),
            'sender_id' => User::factory(),
            'amount' => $amount,
            'commission_fee' => $commission,
            'total_amount_debited' => $commission + $amount
        ];
    }
}
