<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionsChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Receitas vs despesas';

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));

        $transactions = Transaction::query()
            ->where('date', '>=', $months->first()->startOfMonth())
            ->get(['type', 'value', 'date']);

        $revenue = [];
        $expense = [];

        foreach ($months as $month) {
            $monthTransactions = $transactions
                ->filter(fn (Transaction $transaction) => $transaction->date->isSameMonth($month));

            $revenue[] = $monthTransactions
                ->where('type', TransactionType::Revenue)
                ->sum('value');

            $expense[] = $monthTransactions
                ->where('type', TransactionType::Expense)
                ->sum('value');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Receitas',
                    'data' => $revenue,
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Despesas',
                    'data' => $expense,
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->translatedFormat('M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
