<?php

namespace App\Enums;

use Carbon\CarbonImmutable;
use Filament\Support\Contracts\HasLabel;

enum RecurrenceFrequency: string implements HasLabel
{
    case Weekly = 'WEEKLY';
    case Biweekly = 'BIWEEKLY';
    case Monthly = 'MONTHLY';
    case Quarterly = 'QUARTERLY';
    case Semiannual = 'SEMIANNUAL';
    case Yearly = 'YEARLY';

    public function getLabel(): string
    {
        return match ($this) {
            self::Weekly => 'Semanal',
            self::Biweekly => 'Quinzenal',
            self::Monthly => 'Mensal',
            self::Quarterly => 'Trimestral',
            self::Semiannual => 'Semestral',
            self::Yearly => 'Anual',
        };
    }

    /**
     * Date of the nth occurrence counted from the first one (n = 0).
     *
     * Always computed from the first date so that month-end due dates don't drift
     * (e.g. Jan 31 → Feb 28 → Mar 31).
     */
    public function occurrence(CarbonImmutable $firstDate, int $index): CarbonImmutable
    {
        return match ($this) {
            self::Weekly => $firstDate->addWeeks($index),
            self::Biweekly => $firstDate->addWeeks($index * 2),
            self::Monthly => $firstDate->addMonthsNoOverflow($index),
            self::Quarterly => $firstDate->addMonthsNoOverflow($index * 3),
            self::Semiannual => $firstDate->addMonthsNoOverflow($index * 6),
            self::Yearly => $firstDate->addYearsNoOverflow($index),
        };
    }
}
