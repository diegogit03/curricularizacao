<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\BankAccount;
use App\Models\RecurringAccount;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Number;

/**
 * Realized cash flow (transactions up to today) followed by a projection built from
 * the active recurring accounts (from tomorrow on), with the running balance.
 */
class CashFlowChart extends ChartWidget
{
    use InteractsWithPageFilters;

    /**
     * Ranges up to this many days are grouped by day; longer ones are grouped by month.
     */
    public const int DAILY_GROUPING_MAX_DAYS = 62;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Fluxo de caixa';

    protected ?string $maxHeight = '420px';

    /**
     * @var array{labels: list<string>, balances: list<float>, projected: list<bool>, openingBalance: float, closingBalance: float}|null
     */
    protected ?array $cashFlow = null;

    public function getDescription(): string
    {
        $cashFlow = $this->getCashFlow();

        return sprintf(
            'Saldo inicial: %s · Saldo final: %s · Pontos claros são projeções das contas recorrentes.',
            Number::currency($cashFlow['openingBalance'], 'BRL', 'pt_BR'),
            Number::currency($cashFlow['closingBalance'], 'BRL', 'pt_BR'),
        );
    }

    protected function getData(): array
    {
        $cashFlow = $this->getCashFlow();

        $pointColors = array_map(
            fn (bool $isProjected): string => $isProjected ? 'rgba(59, 130, 246, 0.4)' : '#3b82f6',
            $cashFlow['projected'],
        );

        return [
            'datasets' => [
                [
                    'label' => 'Saldo',
                    'data' => $cashFlow['balances'],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'pointBackgroundColor' => $pointColors,
                    'pointBorderColor' => $pointColors,
                    'pointRadius' => 3,
                    'tension' => 0.2,
                    'fill' => true,
                ],
            ],
            'labels' => $cashFlow['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
                        },
                    },
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => context.dataset.label + ': ' + context.parsed.y.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
                        },
                    },
                },
            }
        JS);
    }

    /**
     * @return array{labels: list<string>, balances: list<float>, projected: list<bool>, openingBalance: float, closingBalance: float}
     */
    protected function getCashFlow(): array
    {
        if ($this->cashFlow !== null) {
            return $this->cashFlow;
        }

        [$startDate, $endDate] = $this->getPeriod();

        $today = CarbonImmutable::today();
        $tomorrow = $today->addDay();
        $groupsByDay = $startDate->diffInDays($endDate) <= self::DAILY_GROUPING_MAX_DAYS;
        $bucketKey = fn (CarbonImmutable $date): string => $groupsByDay ? $date->format('Y-m-d') : $date->format('Y-m');

        $buckets = [];

        for ($date = $startDate; $date->lte($endDate); $date = $groupsByDay ? $date->addDay() : $date->addMonthNoOverflow()->startOfMonth()) {
            $bucketEnd = $groupsByDay ? $date : $date->endOfMonth()->startOfDay();

            $buckets[$bucketKey($date)] = [
                'label' => $groupsByDay ? $date->format('d/m') : ucfirst($date->translatedFormat('M/y')),
                'net' => 0.0,
                'projected' => $bucketEnd->gt($today),
            ];
        }

        $openingBalance = (float) BankAccount::query()->sum('initial_balance')
            + $this->realizedBalanceUntil($startDate->min($tomorrow)->subDay());

        if ($startDate->gt($tomorrow)) {
            foreach ($this->projectedOccurrences($tomorrow, $startDate->subDay()) as [, $type, $value]) {
                $openingBalance += $type === TransactionType::Revenue ? $value : -$value;
            }
        }

        $movements = Transaction::query()
            ->whereDate('date', '>=', $startDate->toDateString())
            ->whereDate('date', '<=', $endDate->min($today)->toDateString())
            ->get(['type', 'value', 'date'])
            ->map(fn (Transaction $transaction): array => [CarbonImmutable::parse($transaction->date), $transaction->type, (float) $transaction->value])
            ->all();

        if ($endDate->gte($tomorrow)) {
            array_push($movements, ...$this->projectedOccurrences($startDate->max($tomorrow), $endDate));
        }

        foreach ($movements as [$date, $type, $value]) {
            $key = $bucketKey($date);

            if (! isset($buckets[$key])) {
                continue;
            }

            $buckets[$key]['net'] += $type === TransactionType::Revenue ? $value : -$value;
        }

        $balance = $openingBalance;
        $balances = [];

        foreach ($buckets as $bucket) {
            $balance += $bucket['net'];
            $balances[] = round($balance, 2);
        }

        return $this->cashFlow = [
            'labels' => array_column($buckets, 'label'),
            'balances' => $balances,
            'projected' => array_column($buckets, 'projected'),
            'openingBalance' => round($openingBalance, 2),
            'closingBalance' => round($balance, 2),
        ];
    }

    /**
     * Period chosen in the page filters, defaulting to the current month plus the next five.
     *
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    protected function getPeriod(): array
    {
        $startDate = filled($this->pageFilters['startDate'] ?? null)
            ? CarbonImmutable::parse($this->pageFilters['startDate'])->startOfDay()
            : CarbonImmutable::today()->startOfMonth();

        $endDate = filled($this->pageFilters['endDate'] ?? null)
            ? CarbonImmutable::parse($this->pageFilters['endDate'])->startOfDay()
            : $startDate->addMonthsNoOverflow(5)->endOfMonth()->startOfDay();

        return $startDate->lte($endDate) ? [$startDate, $endDate] : [$endDate, $startDate];
    }

    /**
     * Net result (revenues minus expenses) of the transactions dated up to the given day.
     */
    protected function realizedBalanceUntil(CarbonImmutable $date): float
    {
        $totals = Transaction::query()
            ->whereDate('date', '<=', $date->toDateString())
            ->selectRaw('type, SUM(value) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return (float) ($totals[TransactionType::Revenue->value] ?? 0) - (float) ($totals[TransactionType::Expense->value] ?? 0);
    }

    /**
     * Occurrences of the active recurring accounts within the period.
     *
     * @return list<array{CarbonImmutable, TransactionType, float}>
     */
    protected function projectedOccurrences(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $occurrences = [];

        RecurringAccount::query()
            ->active()
            ->whereDate('starts_at', '<=', $to->toDateString())
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', $from->toDateString()))
            ->get()
            ->each(function (RecurringAccount $recurringAccount) use ($from, $to, &$occurrences): void {
                foreach ($recurringAccount->occurrencesBetween($from, $to) as $date) {
                    $occurrences[] = [$date, $recurringAccount->type, (float) $recurringAccount->value];
                }
            });

        return $occurrences;
    }
}
