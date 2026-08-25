<?php

namespace Database\Factories;

use App\Enums\PayableAccountStatus;
use App\Models\Category;
use App\Models\PayableAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayableAccount>
 */
class PayableAccountFactory extends Factory
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
            'status' => fake()->randomElement(PayableAccountStatus::cases()),
            'value' => fake()->randomFloat(2, 1, 10000),
            'due_at' => fake()->date(),
            'paid_at' => fake()->date(),
            'category_id' => Category::factory(),
        ];
    }
}
