<?php

namespace Tests\Feature;

use App\Ai\Agents\FinanceAgent;
use App\Ai\Tools\CreateCategory;
use App\Ai\Tools\CreateTransaction;
use App\Ai\Tools\ListCategories;
use App\Ai\Tools\ListTransactions;
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
        $tools = collect((new FinanceAgent)->tools());

        $this->assertInstanceOf(CreateCategory::class, $tools[0]);
        $this->assertInstanceOf(CreateTransaction::class, $tools[1]);
        $this->assertInstanceOf(ListCategories::class, $tools[2]);
        $this->assertInstanceOf(ListTransactions::class, $tools[3]);
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

    public function test_list_categories_tool_lists_all_categories(): void
    {
        Category::factory()->create(['name' => 'Renda', 'type' => 'REVENUE']);
        Category::factory()->create(['name' => 'Alimentação', 'type' => 'EXPENSE']);

        $result = (new ListCategories)->handle(new Request);

        $this->assertStringContainsString('Renda', (string) $result);
        $this->assertStringContainsString('Alimentação', (string) $result);
    }

    public function test_list_categories_tool_filters_by_type(): void
    {
        Category::factory()->create(['name' => 'Renda', 'type' => 'REVENUE']);
        Category::factory()->create(['name' => 'Alimentação', 'type' => 'EXPENSE']);

        $result = (new ListCategories)->handle(new Request(['type' => 'REVENUE']));

        $this->assertStringContainsString('Renda', (string) $result);
        $this->assertStringNotContainsString('Alimentação', (string) $result);
    }

    public function test_list_categories_tool_returns_empty_message(): void
    {
        $result = (new ListCategories)->handle(new Request);

        $this->assertSame('Nenhuma categoria encontrada.', (string) $result);
    }

    public function test_list_transactions_tool_lists_and_filters(): void
    {
        $income = Category::factory()->create(['name' => 'Renda', 'type' => 'REVENUE']);
        $expense = Category::factory()->create(['name' => 'Alimentação', 'type' => 'EXPENSE']);

        Transaction::factory()->create([
            'description' => 'Salário',
            'type' => 'REVENUE',
            'value' => 5000,
            'date' => '2026-08-10',
            'category_id' => $income->id,
        ]);
        Transaction::factory()->create([
            'description' => 'Mercado',
            'type' => 'EXPENSE',
            'value' => 300,
            'date' => '2026-08-15',
            'category_id' => $expense->id,
        ]);

        $result = (new ListTransactions)->handle(new Request([
            'type' => 'EXPENSE',
            'category' => 'alimentação',
        ]));

        $this->assertStringContainsString('Mercado', (string) $result);
        $this->assertStringNotContainsString('Salário', (string) $result);

        $byPeriod = (new ListTransactions)->handle(new Request([
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-12',
        ]));

        $this->assertStringContainsString('Salário', (string) $byPeriod);
        $this->assertStringNotContainsString('Mercado', (string) $byPeriod);
    }

    public function test_list_transactions_tool_returns_empty_message(): void
    {
        $result = (new ListTransactions)->handle(new Request);

        $this->assertSame('Nenhuma transação encontrada.', (string) $result);
    }

    public function test_list_transactions_tool_rejects_invalid_input(): void
    {
        $result = (new ListTransactions)->handle(new Request([
            'type' => 'INVALID',
        ]));

        $this->assertStringContainsString('Erros de validação', (string) $result);
    }
}
