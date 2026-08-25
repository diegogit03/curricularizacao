<?php

namespace App\Filament\Widgets;

use App\Enums\PayableAccountStatus;
use App\Enums\ReceivableAccountStatus;
use App\Enums\TransactionType;
use App\Models\PayableAccount;
use App\Models\ReceivableAccount;
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

        $receivablePending = ReceivableAccount::query()
            ->where('status', ReceivableAccountStatus::Pending)
            ->sum('value');

        $payablePending = PayableAccount::query()
            ->where('status', PayableAccountStatus::Pending)
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
                ->description('Contas a receber pendentes')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('warning'),
            Stat::make('A pagar', Number::currency((float) $payablePending, 'BRL', 'pt_BR'))
                ->description('Contas a pagar pendentes')
                ->descriptionIcon(Heroicon::ExclamationCircle)
                ->color('warning'),
        ];
    }
}
