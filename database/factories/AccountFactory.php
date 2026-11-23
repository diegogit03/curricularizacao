<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
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
            'status' => AccountStatus::Pending,
            'value' => fake()->randomFloat(2, 1, 10000),
            'due_at' => fake()->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
            'settled_at' => null,
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

    public function settled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => AccountStatus::Settled,
            'settled_at' => $attributes['due_at'],
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => [
            'status' => AccountStatus::Overdue,
            'due_at' => fake()->dateTimeBetween('-60 days', '-1 day')->format('Y-m-d'),
        ]);
    }
}
