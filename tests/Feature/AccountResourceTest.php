<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use App\Filament\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Resources\Accounts\Pages\EditAccount;
use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_list_opens_on_payable_tab_by_default(): void
    {
        $payable = Account::factory()->payable()->create();
        $receivable = Account::factory()->receivable()->create();

        Livewire::test(ListAccounts::class)
            ->assertSet('activeTab', 'payable')
            ->assertCanSeeTableRecords([$payable])
            ->assertCanNotSeeTableRecords([$receivable]);
    }

    public function test_tabs_filter_accounts_by_type(): void
    {
        $payable = Account::factory()->payable()->create();
        $receivable = Account::factory()->receivable()->create();

        Livewire::test(ListAccounts::class)
            ->set('activeTab', 'payable')
            ->assertCanSeeTableRecords([$payable])
            ->assertCanNotSeeTableRecords([$receivable])
            ->set('activeTab', 'receivable')
            ->assertCanSeeTableRecords([$receivable])
            ->assertCanNotSeeTableRecords([$payable]);
    }

    public function test_accounts_can_be_filtered_by_status(): void
    {
        $pending = Account::factory()->payable()->create();
        $overdue = Account::factory()->payable()->overdue()->create();

        Livewire::test(ListAccounts::class)
            ->filterTable('status', AccountStatus::Overdue->value)
            ->assertCanSeeTableRecords([$overdue])
            ->assertCanNotSeeTableRecords([$pending]);
    }

    public function test_account_can_be_created_as_pending_by_default(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateAccount::class)
            ->assertSchemaStateSet(['status' => AccountStatus::Pending])
            ->fillForm([
                'description' => 'Aluguel',
                'type' => TransactionType::Expense->value,
                'value' => 1400,
                'due_at' => '2026-12-10',
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Account::class, [
            'description' => 'Aluguel',
            'type' => TransactionType::Expense,
            'status' => AccountStatus::Pending,
            'settled_at' => null,
        ]);
    }

    public function test_account_requires_settlement_date_when_created_as_settled(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateAccount::class)
            ->fillForm([
                'description' => 'Consultoria',
                'type' => TransactionType::Revenue->value,
                'status' => AccountStatus::Settled->value,
                'value' => 2200,
                'due_at' => '2026-12-12',
                'settled_at' => null,
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['settled_at' => 'required']);
    }

    public function test_account_requires_mandatory_fields(): void
    {
        Livewire::test(CreateAccount::class)
            ->fillForm([
                'description' => null,
                'type' => null,
                'value' => null,
                'due_at' => null,
                'category_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'description' => 'required',
                'type' => 'required',
                'value' => 'required',
                'due_at' => 'required',
                'category_id' => 'required',
            ]);

        $this->assertDatabaseCount(Account::class, 0);
    }

    public function test_payable_account_can_be_marked_as_paid_from_table(): void
    {
        $account = Account::factory()->payable()->create();

        Livewire::test(ListAccounts::class)
            ->assertActionVisible(TestAction::make('markAsSettled')->table($account))
            ->callAction(TestAction::make('markAsSettled')->table($account), [
                'settled_at' => '2026-11-20',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified('Conta marcada como paga');

        $account->refresh();

        $this->assertSame(AccountStatus::Settled, $account->status);
        $this->assertSame('2026-11-20', $account->settled_at->toDateString());
    }

    public function test_overdue_receivable_account_can_be_marked_as_received(): void
    {
        $account = Account::factory()->receivable()->overdue()->create();

        Livewire::test(ListAccounts::class)
            ->set('activeTab', 'receivable')
            ->callAction(TestAction::make('markAsSettled')->table($account), [
                'settled_at' => '2026-11-21',
            ])
            ->assertNotified('Conta marcada como recebida');

        $this->assertSame(AccountStatus::Settled, $account->refresh()->status);
    }

    public function test_mark_as_settled_requires_a_date(): void
    {
        $account = Account::factory()->payable()->create();

        Livewire::test(ListAccounts::class)
            ->callAction(TestAction::make('markAsSettled')->table($account), [
                'settled_at' => null,
            ])
            ->assertHasFormErrors(['settled_at' => 'required']);

        $this->assertSame(AccountStatus::Pending, $account->refresh()->status);
    }

    public function test_mark_as_settled_action_is_hidden_for_settled_accounts(): void
    {
        $account = Account::factory()->payable()->settled()->create();

        Livewire::test(ListAccounts::class)
            ->assertActionHidden(TestAction::make('markAsSettled')->table($account));
    }

    public function test_account_can_be_marked_as_settled_from_edit_page(): void
    {
        $account = Account::factory()->payable()->create();

        Livewire::test(EditAccount::class, ['record' => $account->id])
            ->callAction('markAsSettled', ['settled_at' => '2026-11-22'])
            ->assertNotified()
            ->assertSchemaStateSet(['status' => AccountStatus::Settled]);

        $this->assertSame(AccountStatus::Settled, $account->refresh()->status);
    }

    public function test_settlement_date_is_cleared_when_account_is_reopened(): void
    {
        $account = Account::factory()->settled()->create();

        Livewire::test(EditAccount::class, ['record' => $account->id])
            ->fillForm(['status' => AccountStatus::Pending->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $account->refresh();

        $this->assertSame(AccountStatus::Pending, $account->status);
        $this->assertNull($account->settled_at);
    }
}
