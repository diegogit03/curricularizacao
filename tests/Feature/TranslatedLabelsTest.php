<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TranslatedLabelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_enums_have_portuguese_labels(): void
    {
        $this->assertSame('Receita', TransactionType::Revenue->getLabel());
        $this->assertSame('Despesa', TransactionType::Expense->getLabel());
        $this->assertSame('Pendente', AccountStatus::Pending->getLabel());
        $this->assertSame('Quitada', AccountStatus::Settled->getLabel());
        $this->assertSame('Vencida', AccountStatus::Overdue->getLabel());
    }

    public function test_settled_status_label_depends_on_account_type(): void
    {
        $this->assertSame('Paga', AccountStatus::Settled->labelFor(TransactionType::Expense));
        $this->assertSame('Recebida', AccountStatus::Settled->labelFor(TransactionType::Revenue));
        $this->assertSame('Vencida', AccountStatus::Overdue->labelFor(TransactionType::Revenue));
        $this->assertSame('Pendente', AccountStatus::Pending->labelFor(TransactionType::Expense));
    }

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function createPagesProvider(): array
    {
        return [
            'transações' => ['/dashboard/transactions/create', ['Descrição', 'Tipo', 'Valor', 'Data', 'Categoria', 'Conta bancária', 'Receita', 'Despesa']],
            'contas' => ['/dashboard/accounts/create', ['Descrição', 'Tipo', 'Status', 'Valor', 'Vencimento', 'Categoria', 'Conta bancária', 'A pagar', 'A receber', 'Pendente', 'Quitada', 'Vencida']],
            'categorias' => ['/dashboard/categories/create', ['Nome', 'Tipo', 'Receita', 'Despesa']],
            'contas bancárias' => ['/dashboard/bank-accounts/create', ['Nome', 'Banco', 'Agência', 'Número da conta', 'Saldo inicial']],
        ];
    }

    /**
     * @param  list<string>  $labels
     */
    #[DataProvider('createPagesProvider')]
    public function test_create_pages_show_portuguese_labels(string $url, array $labels): void
    {
        $response = $this->get($url)->assertOk();

        foreach ($labels as $label) {
            $response->assertSee($label);
        }

        $response->assertDontSeeText('Description')
            ->assertDontSeeText('REVENUE')
            ->assertDontSeeText('PENDING');
    }

    public function test_list_pages_show_portuguese_columns_and_enum_badges(): void
    {
        $bankAccount = BankAccount::factory()->create();
        Transaction::factory()->create(['type' => TransactionType::Revenue, 'bank_account_id' => $bankAccount->id]);
        Account::factory()->payable()->settled()->create();
        Account::factory()->receivable()->settled()->create();
        Account::factory()->payable()->overdue()->create();

        $this->get('/dashboard/transactions')
            ->assertOk()
            ->assertSee(['Descrição', 'Tipo', 'Valor', 'Categoria', 'Conta bancária', 'Receita'])
            ->assertDontSeeText('REVENUE');

        $this->get('/dashboard/accounts?tab=payable')
            ->assertOk()
            ->assertSee(['Vencimento', 'Pago/recebido em', 'A pagar', 'A receber', 'Paga', 'Vencida'])
            ->assertDontSeeText('SETTLED')
            ->assertDontSeeText('OVERDUE')
            ->assertDontSeeText('EXPENSE');

        $this->get('/dashboard/accounts?tab=receivable')
            ->assertOk()
            ->assertSee('Recebida')
            ->assertDontSeeText('SETTLED')
            ->assertDontSeeText('REVENUE');

        $this->get('/dashboard/bank-accounts')
            ->assertOk()
            ->assertSee(['Nome', 'Banco', 'Agência', 'Número da conta', 'Saldo inicial']);
    }
}
