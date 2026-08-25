<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResourcePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_category_resource_pages_render(): void
    {
        $category = Category::factory()->create();

        $this->get('/dashboard/categories')->assertOk();
        $this->get('/dashboard/categories/create')->assertOk();
        $this->get("/dashboard/categories/{$category->id}/edit")->assertOk();
    }

    public function test_transaction_resource_pages_render(): void
    {
        $this->get('/dashboard/transactions')->assertOk();
        $this->get('/dashboard/transactions/create')->assertOk();
    }

    public function test_receivable_account_resource_pages_render(): void
    {
        $this->get('/dashboard/receivable-accounts')->assertOk();
        $this->get('/dashboard/receivable-accounts/create')->assertOk();
    }

    public function test_payable_account_resource_pages_render(): void
    {
        $this->get('/dashboard/payable-accounts')->assertOk();
        $this->get('/dashboard/payable-accounts/create')->assertOk();
    }

    public function test_transaction_can_be_created(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateTransaction::class)
            ->fillForm([
                'description' => 'Test transaction',
                'type' => TransactionType::Revenue,
                'value' => 123.45,
                'date' => '2026-08-25',
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Transaction::class, [
            'description' => 'Test transaction',
            'type' => TransactionType::Revenue,
            'category_id' => $category->id,
        ]);
    }
}
