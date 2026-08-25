<?php

namespace Tests\Feature;

use App\Ai\Agents\FinanceAgent;
use App\Ai\Tools\CreateCategory;
use App\Ai\Tools\CreateTransaction;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class AiToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_agent_exposes_the_tools(): void
    {
        $tools = (new FinanceAgent)->tools();

        $this->assertInstanceOf(CreateCategory::class, $tools[0]);
        $this->assertInstanceOf(CreateTransaction::class, $tools[1]);
    }

    public function test_create_category_tool_creates_a_category(): void
    {
        $result = (new CreateCategory)->handle(new Request([
            'name' => 'Salário',
            'type' => 'REVENUE',
        ]));

        $this->assertStringContainsString('Salário', (string) $result);
        $this->assertDatabaseHas(Category::class, [
            'name' => 'Salário',
            'type' => 'REVENUE',
        ]);
    }

    public function test_create_category_tool_rejects_invalid_input(): void
    {
        $result = (new CreateCategory)->handle(new Request([
            'name' => '',
            'type' => 'INVALID',
        ]));

        $this->assertStringContainsString('Erros de validação', (string) $result);
        $this->assertDatabaseCount(Category::class, 0);
    }

    public function test_create_transaction_tool_creates_a_transaction(): void
    {
        $category = Category::factory()->create([
            'name' => 'Renda',
            'type' => 'REVENUE',
        ]);

        $result = (new CreateTransaction)->handle(new Request([
            'description' => 'Salário de agosto',
            'type' => 'REVENUE',
            'value' => 5000.50,
            'date' => '2026-08-25',
            'category' => 'renda',
        ]));

        $this->assertStringContainsString('Salário de agosto', (string) $result);
        $this->assertDatabaseHas(Transaction::class, [
            'description' => 'Salário de agosto',
            'type' => 'REVENUE',
            'date' => '2026-08-25',
            'category_id' => $category->id,
        ]);
    }

    public function test_create_transaction_tool_creates_missing_category(): void
    {
        $result = (new CreateTransaction)->handle(new Request([
            'description' => 'Almoço',
            'type' => 'EXPENSE',
            'value' => 42.90,
            'category' => 'Alimentação',
        ]));

        $this->assertStringContainsString('criada automaticamente', (string) $result);
        $this->assertDatabaseHas(Category::class, [
            'name' => 'Alimentação',
            'type' => 'EXPENSE',
        ]);
        $this->assertDatabaseHas(Transaction::class, [
            'description' => 'Almoço',
            'type' => 'EXPENSE',
            'date' => now()->toDateString(),
        ]);
    }

    public function test_create_transaction_tool_rejects_invalid_input(): void
    {
        $result = (new CreateTransaction)->handle(new Request([
            'description' => '',
            'type' => 'EXPENSE',
            'value' => 0,
            'category' => 'Alimentação',
        ]));

        $this->assertStringContainsString('Erros de validação', (string) $result);
        $this->assertDatabaseCount(Transaction::class, 0);
        $this->assertDatabaseCount(Category::class, 0);
    }
}
