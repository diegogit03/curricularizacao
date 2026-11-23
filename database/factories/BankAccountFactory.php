<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'bank' => fake()->randomElement(['Banco do Brasil', 'Caixa', 'Itaú', 'Bradesco', 'Nubank', 'Santander']),
            'agency' => fake()->numerify('####'),
            'account_number' => fake()->numerify('#####-#'),
            'initial_balance' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
