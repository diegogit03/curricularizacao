<?php

namespace Tests\Feature;

use App\Enums\RecurrenceFrequency;
use App\Enums\TransactionType;
use App\Filament\Widgets\CashFlowChart;
use App\Models\BankAccount;
use App\Models\RecurringAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-11-23 10:00:00');

        $this->actingAs(User::factory()->create());

        BankAccount::factory()->create(['initial_balance' => 1000]);
    }

    public function test_cash_flow_page_renders_with_date_filters(): void
    {
        $this->get('/dashboard/cash-flow')
            ->assertOk()
            ->assertSee(['Fluxo de Caixa', 'Data inicial', 'Data final'])
            ->assertSeeLivewire(CashFlowChart::class);
    }

    public function test_cash_flow_is_not_shown_on_main_dashboard(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSeeLivewire(CashFlowChart::class);
    }

    public function test_monthly_cash_flow_combines_realized_transactions_and_recurring_projection(): void
    {
        Transaction::factory()->create(['type' => TransactionType::Expense, 'value' => 100, 'date' => '2026-10-15']);
        Transaction::factory()->create(['type' => TransactionType::Revenue, 'value' => 500, 'date' => '2026-11-05']);
        Transaction::factory()->create(['type' => TransactionType::Expense, 'value' => 200, 'date' => '2026-11-10']);

        RecurringAccount::factory()->receivable()->create(['frequency' => RecurrenceFrequency::Monthly, 'value' => 3000, 'starts_at' => '2026-12-05']);
        RecurringAccount::factory()->payable()->create(['frequency' => RecurrenceFrequency::Monthly, 'value' => 1000, 'starts_at' => '2026-11-10']);
        RecurringAccount::factory()->payable()->inactive()->create(['frequency' => RecurrenceFrequency::Monthly, 'value' => 999, 'starts_at' => '2026-11-01']);

        $cashFlow = $this->cashFlow('2026-11-01', '2027-01-31');

        $this->assertSame(['Nov/26', 'Dez/26', 'Jan/27'], $cashFlow['labels']);
        $this->assertSame([1200.0, 3200.0, 5200.0], $cashFlow['balances']);
        $this->assertSame(900.0, $cashFlow['openingBalance']);
        $this->assertSame(5200.0, $cashFlow['closingBalance']);
    }

    public function test_short_periods_are_grouped_by_day_and_projection_starts_tomorrow(): void
    {
        Transaction::factory()->create(['type' => TransactionType::Expense, 'value' => 30, 'date' => '2026-11-21']);
        RecurringAccount::factory()->payable()->create(['frequency' => RecurrenceFrequency::Weekly, 'value' => 50, 'starts_at' => '2026-11-02']);

        $cashFlow = $this->cashFlow('2026-11-20', '2026-11-30');

        $this->assertCount(11, $cashFlow['labels']);
        $this->assertSame('20/11', $cashFlow['labels'][0]);
        $this->assertSame([1000.0, 970.0, 970.0, 970.0, 970.0, 970.0, 970.0, 970.0, 970.0, 970.0, 920.0], $cashFlow['balances']);
        $this->assertSame([false, false, false, false, true, true, true, true, true, true, true], $cashFlow['projected']);
        $this->assertSame(1000.0, $cashFlow['openingBalance']);
        $this->assertSame(920.0, $cashFlow['closingBalance']);
    }

    public function test_future_period_opening_balance_includes_projection_until_start(): void
    {
        Transaction::factory()->create(['type' => TransactionType::Revenue, 'value' => 250, 'date' => '2026-11-01']);
        RecurringAccount::factory()->payable()->create(['frequency' => RecurrenceFrequency::Monthly, 'value' => 100, 'starts_at' => '2026-12-10']);

        $cashFlow = $this->cashFlow('2027-01-01', '2027-01-31');

        $this->assertSame(1150.0, $cashFlow['openingBalance']);
        $this->assertSame(1050.0, $cashFlow['closingBalance']);
    }

    public function test_chart_shows_only_the_balance_line(): void
    {
        $widget = Livewire::test(CashFlowChart::class)->instance();

        $data = (fn (): array => $this->getData())->call($widget);
        $type = (fn (): string => $this->getType())->call($widget);

        $this->assertSame('line', $type);
        $this->assertSame(['Saldo'], array_column($data['datasets'], 'label'));
    }

    public function test_inverted_period_is_swapped(): void
    {
        $this->assertSame(
            $this->cashFlow('2026-11-01', '2027-01-31')['labels'],
            $this->cashFlow('2027-01-31', '2026-11-01')['labels'],
        );
    }

    public function test_chart_uses_default_period_without_filters(): void
    {
        Livewire::test(CashFlowChart::class)
            ->assertOk()
            ->assertSee('Fluxo de caixa')
            ->assertSee('Saldo inicial');

        $this->assertSame(['Nov/26', 'Dez/26', 'Jan/27', 'Fev/27', 'Mar/27', 'Abr/27'], $this->cashFlow(null, null)['labels']);
    }

    /**
     * @return array{labels: list<string>, balances: list<float>, projected: list<bool>, openingBalance: float, closingBalance: float}
     */
    private function cashFlow(?string $startDate, ?string $endDate): array
    {
        $widget = Livewire::test(CashFlowChart::class, [
            'pageFilters' => ['startDate' => $startDate, 'endDate' => $endDate],
        ])->instance();

        return (fn (): array => $this->getCashFlow())->call($widget);
    }
}
