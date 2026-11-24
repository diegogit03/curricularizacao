<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\RecurrenceFrequency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\RecurringAccount;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FinancialDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a realistic financial dataset so the screens can be captured.
     */
    public function run(): void
    {
        $this->seedBankAccounts();

        $this->seedCategories();

        $this->seedTransactions();

        $this->seedPayableAccounts();

        $this->seedReceivableAccounts();

        $this->seedRecurringAccounts();

        $this->assignBankAccounts();

        Account::query()->pastDue()->update(['status' => AccountStatus::Overdue]);
    }

    private function seedBankAccounts(): void
    {
        BankAccount::firstOrCreate(
            ['name' => 'Conta Corrente'],
            ['bank' => 'Banco do Brasil', 'agency' => '1234', 'account_number' => '56789-0', 'initial_balance' => 2500.00],
        );

        BankAccount::firstOrCreate(
            ['name' => 'Conta Digital'],
            ['bank' => 'Nubank', 'agency' => '0001', 'account_number' => '12345-6', 'initial_balance' => 800.00],
        );
    }

    /**
     * Link revenues to the digital account and expenses to the checking account.
     */
    private function assignBankAccounts(): void
    {
        $checking = BankAccount::where('name', 'Conta Corrente')->first();
        $digital = BankAccount::where('name', 'Conta Digital')->first();

        Transaction::whereNull('bank_account_id')
            ->where('type', TransactionType::Revenue)
            ->update(['bank_account_id' => $digital->id]);

        Transaction::whereNull('bank_account_id')
            ->where('type', TransactionType::Expense)
            ->update(['bank_account_id' => $checking->id]);

        Account::whereNull('bank_account_id')
            ->where('type', TransactionType::Expense)
            ->update(['bank_account_id' => $checking->id]);

        Account::whereNull('bank_account_id')
            ->where('type', TransactionType::Revenue)
            ->update(['bank_account_id' => $digital->id]);

        RecurringAccount::whereNull('bank_account_id')
            ->where('type', TransactionType::Expense)
            ->update(['bank_account_id' => $checking->id]);

        RecurringAccount::whereNull('bank_account_id')
            ->where('type', TransactionType::Revenue)
            ->update(['bank_account_id' => $digital->id]);
    }

    /**
     * Fixed revenues and expenses that feed the cash flow projection.
     */
    private function seedRecurringAccounts(): void
    {
        $nextMonth = now()->addMonthNoOverflow()->startOfMonth();

        $items = [
            ['description' => 'Salário', 'type' => TransactionType::Revenue, 'frequency' => RecurrenceFrequency::Monthly, 'value' => 5200.00, 'day' => 5, 'category' => 'Salário'],
            ['description' => 'Contrato de consultoria', 'type' => TransactionType::Revenue, 'frequency' => RecurrenceFrequency::Monthly, 'value' => 1800.00, 'day' => 15, 'category' => 'Serviços'],
            ['description' => 'Aluguel do imóvel', 'type' => TransactionType::Expense, 'frequency' => RecurrenceFrequency::Monthly, 'value' => 1400.00, 'day' => 10, 'category' => 'Aluguel'],
            ['description' => 'Internet fibra', 'type' => TransactionType::Expense, 'frequency' => RecurrenceFrequency::Monthly, 'value' => 129.90, 'day' => 20, 'category' => 'Internet'],
            ['description' => 'Plano de saúde', 'type' => TransactionType::Expense, 'frequency' => RecurrenceFrequency::Monthly, 'value' => 540.00, 'day' => 25, 'category' => 'Saúde'],
            ['description' => 'Supermercado', 'type' => TransactionType::Expense, 'frequency' => RecurrenceFrequency::Weekly, 'value' => 195.00, 'day' => 3, 'category' => 'Supermercado'],
            ['description' => 'Seguro do carro', 'type' => TransactionType::Expense, 'frequency' => RecurrenceFrequency::Semiannual, 'value' => 1250.00, 'day' => 15, 'category' => 'Transporte'],
        ];

        foreach ($items as $item) {
            RecurringAccount::firstOrCreate(
                ['description' => $item['description'], 'type' => $item['type']],
                [
                    'frequency' => $item['frequency'],
                    'value' => $item['value'],
                    'starts_at' => $nextMonth->copy()->day($item['day']),
                    'category_id' => Category::where('name', $item['category'])->first()->id,
                ],
            );
        }
    }

    private function seedCategories(): void
    {
        $revenue = ['Salário', 'Vendas', 'Serviços', 'Freelance', 'Investimentos'];
        $expense = [
            'Aluguel', 'Supermercado', 'Transporte', 'Energia Elétrica', 'Água',
            'Internet', 'Lazer', 'Saúde', 'Educação', 'Restaurante', 'Vestuário',
        ];

        foreach ($revenue as $name) {
            Category::firstOrCreate(['name' => $name, 'type' => TransactionType::Revenue]);
        }

        foreach ($expense as $name) {
            Category::firstOrCreate(['name' => $name, 'type' => TransactionType::Expense]);
        }
    }

    private function seedTransactions(): void
    {
        $salary = Category::where('name', 'Salário')->first();
        $supermarket = Category::where('name', 'Supermercado')->first();
        $rent = Category::where('name', 'Aluguel')->first();
        $transport = Category::where('name', 'Transporte')->first();
        $energy = Category::where('name', 'Energia Elétrica')->first();
        $water = Category::where('name', 'Água')->first();
        $internet = Category::where('name', 'Internet')->first();
        $leisure = Category::where('name', 'Lazer')->first();
        $health = Category::where('name', 'Saúde')->first();
        $education = Category::where('name', 'Educação')->first();
        $restaurant = Category::where('name', 'Restaurante')->first();
        $freelance = Category::where('name', 'Freelance')->first();
        $sales = Category::where('name', 'Vendas')->first();

        $months = collect([3, 2, 1, 0]);
        $months->each(function (int $offset) use (
            $salary, $supermarket, $rent, $transport, $energy,
            $water, $internet, $leisure, $health, $education, $restaurant, $freelance, $sales,
        ) {
            $date = now()->subMonths($offset);

            Transaction::create([
                'description' => 'Salário',
                'type' => TransactionType::Revenue,
                'value' => 5200.00,
                'date' => $date->copy()->day(5),
                'category_id' => $salary->id,
            ]);

            Transaction::create([
                'description' => 'Venda de produtos',
                'type' => TransactionType::Revenue,
                'value' => 1850.00,
                'date' => $date->copy()->day(15),
                'category_id' => $sales->id,
            ]);

            Transaction::create([
                'description' => 'Projeto freelance',
                'type' => TransactionType::Revenue,
                'value' => 950.00,
                'date' => $date->copy()->day(22),
                'category_id' => $freelance->id,
            ]);

            Transaction::create([
                'description' => 'Aluguel do imóvel',
                'type' => TransactionType::Expense,
                'value' => 1400.00,
                'date' => $date->copy()->day(10),
                'category_id' => $rent->id,
            ]);

            Transaction::create([
                'description' => 'Compra mensal no supermercado',
                'type' => TransactionType::Expense,
                'value' => 780.50,
                'date' => $date->copy()->day(12),
                'category_id' => $supermarket->id,
            ]);

            Transaction::create([
                'description' => 'Combustível',
                'type' => TransactionType::Expense,
                'value' => 320.00,
                'date' => $date->copy()->day(8),
                'category_id' => $transport->id,
            ]);

            Transaction::create([
                'description' => 'Conta de energia',
                'type' => TransactionType::Expense,
                'value' => 210.35,
                'date' => $date->copy()->day(18),
                'category_id' => $energy->id,
            ]);

            Transaction::create([
                'description' => 'Conta de água',
                'type' => TransactionType::Expense,
                'value' => 95.80,
                'date' => $date->copy()->day(18),
                'category_id' => $water->id,
            ]);

            Transaction::create([
                'description' => 'Internet fibra',
                'type' => TransactionType::Expense,
                'value' => 129.90,
                'date' => $date->copy()->day(20),
                'category_id' => $internet->id,
            ]);

            Transaction::create([
                'description' => 'Plano de saúde',
                'type' => TransactionType::Expense,
                'value' => 540.00,
                'date' => $date->copy()->day(25),
                'category_id' => $health->id,
            ]);

            Transaction::create([
                'description' => 'Curso online',
                'type' => TransactionType::Expense,
                'value' => 240.00,
                'date' => $date->copy()->day(27),
                'category_id' => $education->id,
            ]);

            Transaction::create([
                'description' => 'Jantar em família',
                'type' => TransactionType::Expense,
                'value' => 185.00,
                'date' => $date->copy()->day(28),
                'category_id' => $restaurant->id,
            ]);

            Transaction::create([
                'description' => 'Cinema e lazer',
                'type' => TransactionType::Expense,
                'value' => 120.00,
                'date' => $date->copy()->day(30),
                'category_id' => $leisure->id,
            ]);
        });
    }

    private function seedPayableAccounts(): void
    {
        $rent = Category::where('name', 'Aluguel')->first();
        $energy = Category::where('name', 'Energia Elétrica')->first();
        $internet = Category::where('name', 'Internet')->first();
        $transport = Category::where('name', 'Transporte')->first();
        $supermarket = Category::where('name', 'Supermercado')->first();
        $education = Category::where('name', 'Educação')->first();
        $water = Category::where('name', 'Água')->first();

        $paid = [
            ['description' => 'Aluguel - setembro', 'value' => 1400.00, 'due' => -2, 'paid' => -1, 'category' => $rent],
            ['description' => 'Energia elétrica - agosto', 'value' => 210.35, 'due' => -14, 'paid' => -12, 'category' => $energy],
            ['description' => 'Internet - agosto', 'value' => 129.90, 'due' => -11, 'paid' => -9, 'category' => $internet],
            ['description' => 'Parcela do veículo', 'value' => 650.00, 'due' => -20, 'paid' => -18, 'category' => $transport],
        ];

        foreach ($paid as $item) {
            Account::create([
                'description' => $item['description'],
                'type' => TransactionType::Expense,
                'status' => AccountStatus::Settled,
                'value' => $item['value'],
                'due_at' => now()->addDays($item['due']),
                'settled_at' => now()->addDays($item['paid']),
                'category_id' => $item['category']->id,
            ]);
        }

        $pending = [
            ['description' => 'Aluguel - outubro', 'value' => 1400.00, 'due' => 9, 'category' => $rent],
            ['description' => 'Energia elétrica - setembro', 'value' => 198.40, 'due' => 5, 'category' => $energy],
            ['description' => 'Internet - setembro', 'value' => 129.90, 'due' => 3, 'category' => $internet],
            ['description' => 'Matrícula do curso', 'value' => 480.00, 'due' => 15, 'category' => $education],
            ['description' => 'Água e esgoto', 'value' => 92.50, 'due' => 12, 'category' => $water],
            ['description' => 'Condomínio', 'value' => 320.00, 'due' => 20, 'category' => $rent],
            ['description' => 'IPVA - parcela 1', 'value' => 410.00, 'due' => -4, 'category' => $transport],
        ];

        foreach ($pending as $item) {
            Account::create([
                'description' => $item['description'],
                'type' => TransactionType::Expense,
                'status' => AccountStatus::Pending,
                'value' => $item['value'],
                'due_at' => now()->addDays($item['due']),
                'category_id' => $item['category']->id,
            ]);
        }
    }

    private function seedReceivableAccounts(): void
    {
        $sales = Category::where('name', 'Vendas')->first();
        $freelance = Category::where('name', 'Freelance')->first();
        $services = Category::where('name', 'Serviços')->first();

        $received = [
            ['description' => 'Venda de equipamentos usados', 'value' => 850.00, 'due' => -8, 'received' => -6, 'category' => $sales],
            ['description' => 'Projeto de site - parcela 1', 'value' => 1500.00, 'due' => -25, 'received' => -22, 'category' => $freelance],
        ];

        foreach ($received as $item) {
            Account::create([
                'description' => $item['description'],
                'type' => TransactionType::Revenue,
                'status' => AccountStatus::Settled,
                'value' => $item['value'],
                'due_at' => now()->addDays($item['due']),
                'settled_at' => now()->addDays($item['received']),
                'category_id' => $item['category']->id,
            ]);
        }

        $pending = [
            ['description' => 'Venda de produto sob encomenda', 'value' => 620.00, 'due' => 6, 'category' => $sales],
            ['description' => 'Projeto de site - parcela 2', 'value' => 1500.00, 'due' => 18, 'category' => $freelance],
            ['description' => 'Consultoria empresarial', 'value' => 2200.00, 'due' => 12, 'category' => $services],
            ['description' => 'Serviço de manutenção', 'value' => 380.00, 'due' => 25, 'category' => $services],
        ];

        foreach ($pending as $item) {
            Account::create([
                'description' => $item['description'],
                'type' => TransactionType::Revenue,
                'status' => AccountStatus::Pending,
                'value' => $item['value'],
                'due_at' => now()->addDays($item['due']),
                'category_id' => $item['category']->id,
            ]);
        }
    }
}
