<?php

namespace App\Filament\Widgets;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $revenue = Transaction::query()
            ->where('type', TransactionType::Revenue)
            ->sum('value');

        $expense = Transaction::query()
            ->where('type', TransactionType::Expense)
            ->sum('value');

        $receivablePending = Account::query()
            ->where('type', TransactionType::Revenue)
            ->whereIn('status', [AccountStatus::Pending, AccountStatus::Overdue])
            ->sum('value');

        $payablePending = Account::query()
            ->where('type', TransactionType::Expense)
            ->whereIn('status', [AccountStatus::Pending, AccountStatus::Overdue])
            ->sum('value');

        return [
            Stat::make('Receitas', Number::currency((float) $revenue, 'BRL', 'pt_BR'))
                ->description('Total de receitas')
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->color('success'),
            Stat::make('Despesas', Number::currency((float) $expense, 'BRL', 'pt_BR'))
                ->description('Total de despesas')
                ->descriptionIcon(Heroicon::ArrowTrendingDown)
                ->color('danger'),
            Stat::make('A receber', Number::currency((float) $receivablePending, 'BRL', 'pt_BR'))
                ->description('Contas a receber em aberto')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('warning'),
            Stat::make('A pagar', Number::currency((float) $payablePending, 'BRL', 'pt_BR'))
                ->description('Contas a pagar em aberto')
                ->descriptionIcon(Heroicon::ExclamationCircle)
                ->color('warning'),
        ];
    }
}
