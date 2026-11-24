<?php

namespace Database\Factories;

use App\Enums\RecurrenceFrequency;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\RecurringAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringAccount>
 */
class RecurringAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(3),
            'type' => fake()->randomElement(TransactionType::cases()),
            'frequency' => RecurrenceFrequency::Monthly,
            'value' => fake()->randomFloat(2, 1, 10000),
            'starts_at' => fake()->dateTimeBetween('-30 days', '+30 days')->format('Y-m-d'),
            'ends_at' => null,
            'is_active' => true,
            'category_id' => Category::factory(),
        ];
    }

    public function payable(): static
    {
        return $this->state(fn (): array => ['type' => TransactionType::Expense]);
    }

    public function receivable(): static
    {
        return $this->state(fn (): array => ['type' => TransactionType::Revenue]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
