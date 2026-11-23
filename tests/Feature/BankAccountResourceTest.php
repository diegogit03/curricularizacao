<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use App\Filament\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Resources\BankAccounts\Pages\CreateBankAccount;
use App\Filament\Resources\BankAccounts\Pages\EditBankAccount;
use App\Filament\Resources\BankAccounts\Pages\ListBankAccounts;
use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BankAccountResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_bank_account_resource_pages_render(): void
    {
        $bankAccount = BankAccount::factory()->create();

        $this->get('/dashboard/bank-accounts')->assertOk();
        $this->get('/dashboard/bank-accounts/create')->assertOk();
        $this->get("/dashboard/bank-accounts/{$bankAccount->id}/edit")->assertOk();
    }

    public function test_bank_accounts_are_listed(): void
    {
        $bankAccounts = BankAccount::factory()->count(3)->create();

        Livewire::test(ListBankAccounts::class)
            ->assertCanSeeTableRecords($bankAccounts);
    }

    public function test_bank_account_can_be_created(): void
    {
        Livewire::test(CreateBankAccount::class)
            ->fillForm([
                'name' => 'Conta Corrente',
                'bank' => 'Banco do Brasil',
                'agency' => '1234',
                'account_number' => '56789-0',
                'initial_balance' => 1500.50,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(BankAccount::class, [
            'name' => 'Conta Corrente',
            'bank' => 'Banco do Brasil',
            'agency' => '1234',
            'account_number' => '56789-0',
            'initial_balance' => 1500.50,
        ]);
    }

    public function test_bank_account_can_be_created_without_agency_and_account_number(): void
    {
        Livewire::test(CreateBankAccount::class)
            ->fillForm([
                'name' => 'Carteira',
                'bank' => 'Dinheiro',
                'initial_balance' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(BankAccount::class, [
            'name' => 'Carteira',
            'agency' => null,
            'account_number' => null,
        ]);
    }

    public function test_bank_account_requires_name_bank_and_initial_balance(): void
    {
        Livewire::test(CreateBankAccount::class)
            ->fillForm([
                'name' => null,
                'bank' => null,
                'initial_balance' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'bank' => 'required',
                'initial_balance' => 'required',
            ]);

        $this->assertDatabaseCount(BankAccount::class, 0);
    }

    public function test_bank_account_initial_balance_must_be_numeric(): void
    {
        Livewire::test(CreateBankAccount::class)
            ->fillForm([
                'name' => 'Conta',
                'bank' => 'Banco',
                'initial_balance' => 'abc',
            ])
            ->call('create')
            ->assertHasFormErrors(['initial_balance' => 'numeric']);
    }

    public function test_bank_account_can_be_updated(): void
    {
        $bankAccount = BankAccount::factory()->create();

        Livewire::test(EditBankAccount::class, ['record' => $bankAccount->id])
            ->fillForm(['name' => 'Conta Atualizada'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(BankAccount::class, [
            'id' => $bankAccount->id,
            'name' => 'Conta Atualizada',
        ]);
    }

    public function test_transaction_can_be_created_with_bank_account(): void
    {
        $category = Category::factory()->create();
        $bankAccount = BankAccount::factory()->create();

        Livewire::test(CreateTransaction::class)
            ->fillForm([
                'description' => 'Salário',
                'type' => TransactionType::Revenue,
                'value' => 5000,
                'date' => '2026-11-05',
                'category_id' => $category->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Transaction::class, [
            'description' => 'Salário',
            'bank_account_id' => $bankAccount->id,
        ]);
    }

    public function test_transaction_can_be_created_without_bank_account(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateTransaction::class)
            ->fillForm([
                'description' => 'Sem conta',
                'type' => TransactionType::Expense,
                'value' => 10,
                'date' => '2026-11-05',
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Transaction::class, [
            'description' => 'Sem conta',
            'bank_account_id' => null,
        ]);
    }

    public function test_transaction_bank_account_can_be_changed(): void
    {
        $transaction = Transaction::factory()->create();
        $bankAccount = BankAccount::factory()->create();

        Livewire::test(EditTransaction::class, ['record' => $transaction->id])
            ->fillForm(['bank_account_id' => $bankAccount->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($bankAccount->id, $transaction->refresh()->bank_account_id);
    }

    public function test_transactions_can_be_filtered_by_bank_account(): void
    {
        $bankAccount = BankAccount::factory()->create();
        $linked = Transaction::factory()->create(['bank_account_id' => $bankAccount->id]);
        $others = Transaction::factory()->count(2)->create();

        Livewire::test(ListTransactions::class)
            ->filterTable('bankAccount', $bankAccount->id)
            ->assertCanSeeTableRecords([$linked])
            ->assertCanNotSeeTableRecords($others);
    }

    public function test_payable_account_can_be_created_with_bank_account(): void
    {
        $category = Category::factory()->create();
        $bankAccount = BankAccount::factory()->create();

        Livewire::test(CreateAccount::class)
            ->fillForm([
                'description' => 'Aluguel',
                'type' => TransactionType::Expense,
                'status' => AccountStatus::Pending,
                'value' => 1400,
                'due_at' => '2026-12-10',
                'category_id' => $category->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Account::class, [
            'description' => 'Aluguel',
            'type' => TransactionType::Expense,
            'bank_account_id' => $bankAccount->id,
        ]);
    }

    public function test_receivable_account_can_be_created_with_bank_account(): void
    {
        $category = Category::factory()->create();
        $bankAccount = BankAccount::factory()->create();

        Livewire::test(CreateAccount::class)
            ->fillForm([
                'description' => 'Consultoria',
                'type' => TransactionType::Revenue,
                'status' => AccountStatus::Pending,
                'value' => 2200,
                'due_at' => '2026-12-12',
                'category_id' => $category->id,
                'bank_account_id' => $bankAccount->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Account::class, [
            'description' => 'Consultoria',
            'type' => TransactionType::Revenue,
            'bank_account_id' => $bankAccount->id,
        ]);
    }

    public function test_bank_account_relationships(): void
    {
        $bankAccount = BankAccount::factory()->create();
        Transaction::factory()->count(2)->create(['bank_account_id' => $bankAccount->id]);
        Account::factory()->payable()->create(['bank_account_id' => $bankAccount->id]);
        Account::factory()->receivable()->create(['bank_account_id' => $bankAccount->id]);

        $this->assertCount(2, $bankAccount->transactions);
        $this->assertCount(2, $bankAccount->accounts);
        $this->assertTrue($bankAccount->transactions->first()->bankAccount->is($bankAccount));
    }

    public function test_force_deleting_bank_account_unlinks_records(): void
    {
        $bankAccount = BankAccount::factory()->create();
        $transaction = Transaction::factory()->create(['bank_account_id' => $bankAccount->id]);
        $payable = Account::factory()->payable()->create(['bank_account_id' => $bankAccount->id]);

        $bankAccount->forceDelete();

        $this->assertNull($transaction->refresh()->bank_account_id);
        $this->assertNull($payable->refresh()->bank_account_id);
    }
}
