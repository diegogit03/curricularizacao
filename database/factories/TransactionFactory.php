<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
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
        return [
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(TransactionType::cases()),
            'value' => fake()->randomFloat(2, 1, 10000),
            'date' => fake()->date(),
            'category_id' => Category::factory(),
        ];
    }
}
