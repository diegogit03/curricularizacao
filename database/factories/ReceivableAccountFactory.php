<?php

namespace Database\Factories;

use App\Enums\ReceivableAccountStatus;
use App\Models\Category;
use App\Models\ReceivableAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReceivableAccount>
 */
class ReceivableAccountFactory extends Factory
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
            'status' => fake()->randomElement(ReceivableAccountStatus::cases()),
            'value' => fake()->randomFloat(2, 1, 10000),
            'due_at' => fake()->date(),
            'received_at' => fake()->date(),
            'category_id' => Category::factory(),
        ];
    }
}
