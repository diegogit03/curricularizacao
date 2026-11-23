<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasLabel
{
    case Revenue = 'REVENUE';
    case Expense = 'EXPENSE';

    public function getLabel(): string
    {
        return match ($this) {
            self::Revenue => 'Receita',
            self::Expense => 'Despesa',
        };
    }
}
