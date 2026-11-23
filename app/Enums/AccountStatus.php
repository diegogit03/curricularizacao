<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccountStatus: string implements HasColor, HasLabel
{
    case Pending = 'PENDING';
    case Settled = 'SETTLED';
    case Overdue = 'OVERDUE';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Settled => 'Quitada',
            self::Overdue => 'Vencida',
        };
    }

    /**
     * Label that matches the account type: a settled expense is "Paga" and a settled revenue is "Recebida".
     */
    public function labelFor(TransactionType $type): string
    {
        if ($this !== self::Settled) {
            return $this->getLabel();
        }

        return $type === TransactionType::Revenue ? 'Recebida' : 'Paga';
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Settled => 'success',
            self::Overdue => 'danger',
        };
    }
}
