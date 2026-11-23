<?php

namespace Database\Seeders;

use App\Enums\PayableAccountStatus;
use App\Enums\ReceivableAccountStatus;
use App\Enums\TransactionType;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\PayableAccount;
use App\Models\ReceivableAccount;
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

        $this->assignBankAccounts();
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

        PayableAccount::whereNull('bank_account_id')->update(['bank_account_id' => $checking->id]);

        ReceivableAccount::whereNull('bank_account_id')->update(['bank_account_id' => $digital->id]);
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
            PayableAccount::create([
                'description' => $item['description'],
                'status' => PayableAccountStatus::Paid,
                'value' => $item['value'],
                'due_at' => now()->addDays($item['due']),
                'paid_at' => now()->addDays($item['paid']),
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
        ];

        foreach ($pending as $item) {
            PayableAccount::create([
                'description' => $item['description'],
                'status' => PayableAccountStatus::Pending,
                'value' => $item['value'],
                'due_at' => now()->addDays($item['due']),
                'paid_at' => now()->addDays($item['due']),
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
            ReceivableAccount::create([
                'description' => $item['description'],
                'status' => ReceivableAccountStatus::Received,
                'value' => $item['value'],
                'due_at' => now()->addDays($item['due']),
                'received_at' => now()->addDays($item['received']),
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
            ReceivableAccount::create([
                'description' => $item['description'],
                'status' => ReceivableAccountStatus::Pending,
                'value' => $item['value'],
                'due_at' => now()->addDays($item['due']),
                'received_at' => now()->addDays($item['due']),
                'category_id' => $item['category']->id,
            ]);
        }
    }
}
