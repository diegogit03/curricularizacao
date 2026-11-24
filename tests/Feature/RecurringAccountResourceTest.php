<?php

namespace Tests\Feature;

use App\Enums\RecurrenceFrequency;
use App\Enums\TransactionType;
use App\Filament\Resources\RecurringAccounts\Pages\CreateRecurringAccount;
use App\Filament\Resources\RecurringAccounts\Pages\EditRecurringAccount;
use App\Filament\Resources\RecurringAccounts\Pages\ListRecurringAccounts;
use App\Models\Category;
use App\Models\RecurringAccount;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RecurringAccountResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_resource_pages_render(): void
    {
        $recurringAccount = RecurringAccount::factory()->create();

        $this->get('/dashboard/recurring-accounts')->assertOk()->assertSee('Contas Recorrentes');
        $this->get('/dashboard/recurring-accounts/create')
            ->assertOk()
            ->assertSee(['Descrição', 'Tipo', 'Valor', 'Frequência', 'Primeiro vencimento', 'Último vencimento', 'Ativa', 'Mensal']);
        $this->get("/dashboard/recurring-accounts/{$recurringAccount->id}/edit")->assertOk();
    }

    public function test_tabs_filter_recurring_accounts_by_type(): void
    {
        $payable = RecurringAccount::factory()->payable()->create();
        $receivable = RecurringAccount::factory()->receivable()->create();

        Livewire::test(ListRecurringAccounts::class)
            ->assertSet('activeTab', 'payable')
            ->assertCanSeeTableRecords([$payable])
            ->assertCanNotSeeTableRecords([$receivable])
            ->set('activeTab', 'receivable')
            ->assertCanSeeTableRecords([$receivable])
            ->assertCanNotSeeTableRecords([$payable]);
    }

    public function test_recurring_account_can_be_created_as_monthly_and_active_by_default(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateRecurringAccount::class)
            ->assertSchemaStateSet(['frequency' => RecurrenceFrequency::Monthly, 'is_active' => true])
            ->fillForm([
                'description' => 'Aluguel',
                'type' => TransactionType::Expense->value,
                'value' => 1400,
                'starts_at' => '2026-12-10',
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(RecurringAccount::class, [
            'description' => 'Aluguel',
            'type' => TransactionType::Expense,
            'frequency' => RecurrenceFrequency::Monthly,
            'is_active' => true,
            'ends_at' => null,
        ]);
    }

    public function test_recurring_account_requires_mandatory_fields(): void
    {
        Livewire::test(CreateRecurringAccount::class)
            ->fillForm([
                'description' => null,
                'type' => null,
                'value' => null,
                'frequency' => null,
                'starts_at' => null,
                'category_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'description' => 'required',
                'type' => 'required',
                'value' => 'required',
                'frequency' => 'required',
                'starts_at' => 'required',
                'category_id' => 'required',
            ]);

        $this->assertDatabaseCount(RecurringAccount::class, 0);
    }

    public function test_end_date_cannot_be_before_start_date(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateRecurringAccount::class)
            ->fillForm([
                'description' => 'Academia',
                'type' => TransactionType::Expense->value,
                'value' => 99.9,
                'frequency' => RecurrenceFrequency::Monthly->value,
                'starts_at' => '2026-12-10',
                'ends_at' => '2026-12-01',
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['ends_at' => 'after_or_equal']);
    }

    public function test_recurring_account_can_be_deactivated(): void
    {
        $recurringAccount = RecurringAccount::factory()->create();

        Livewire::test(EditRecurringAccount::class, ['record' => $recurringAccount->id])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($recurringAccount->refresh()->is_active);
    }

    /**
     * @return array<string, array{RecurrenceFrequency, string, string, list<string>}>
     */
    public static function occurrencesProvider(): array
    {
        return [
            'semanal' => [RecurrenceFrequency::Weekly, '2026-12-01', '2026-12-31', ['2026-12-01', '2026-12-08', '2026-12-15', '2026-12-22', '2026-12-29']],
            'quinzenal' => [RecurrenceFrequency::Biweekly, '2026-12-01', '2026-12-31', ['2026-12-01', '2026-12-15', '2026-12-29']],
            'mensal sem deslizar no fim do mês' => [RecurrenceFrequency::Monthly, '2027-01-31', '2027-04-30', ['2027-01-31', '2027-02-28', '2027-03-31', '2027-04-30']],
            'trimestral' => [RecurrenceFrequency::Quarterly, '2026-12-01', '2027-12-31', ['2026-12-01', '2027-03-01', '2027-06-01', '2027-09-01', '2027-12-01']],
            'semestral' => [RecurrenceFrequency::Semiannual, '2026-12-01', '2027-12-31', ['2026-12-01', '2027-06-01', '2027-12-01']],
            'anual' => [RecurrenceFrequency::Yearly, '2026-12-01', '2028-12-31', ['2026-12-01', '2027-12-01', '2028-12-01']],
        ];
    }

    /**
     * @param  list<string>  $expected
     */
    #[DataProvider('occurrencesProvider')]
    public function test_occurrences_follow_the_frequency(RecurrenceFrequency $frequency, string $startsAt, string $to, array $expected): void
    {
        $recurringAccount = RecurringAccount::factory()->make([
            'frequency' => $frequency,
            'starts_at' => $startsAt,
        ]);

        $occurrences = $recurringAccount->occurrencesBetween(CarbonImmutable::parse($startsAt), CarbonImmutable::parse($to));

        $this->assertSame($expected, array_map(fn (CarbonImmutable $date): string => $date->toDateString(), $occurrences));
    }

    public function test_occurrences_respect_period_and_end_date(): void
    {
        $recurringAccount = RecurringAccount::factory()->make([
            'frequency' => RecurrenceFrequency::Monthly,
            'starts_at' => '2026-01-10',
            'ends_at' => '2026-05-10',
        ]);

        $occurrences = $recurringAccount->occurrencesBetween(CarbonImmutable::parse('2026-03-01'), CarbonImmutable::parse('2026-12-31'));

        $this->assertSame(['2026-03-10', '2026-04-10', '2026-05-10'], array_map(fn (CarbonImmutable $date): string => $date->toDateString(), $occurrences));
    }

    public function test_inactive_recurring_account_has_no_occurrences(): void
    {
        $recurringAccount = RecurringAccount::factory()->inactive()->make(['starts_at' => '2026-01-10']);

        $this->assertSame([], $recurringAccount->occurrencesBetween(CarbonImmutable::parse('2026-01-01'), CarbonImmutable::parse('2026-12-31')));
        $this->assertNull($recurringAccount->nextOccurrence(CarbonImmutable::parse('2026-01-01')));
    }

    public function test_next_occurrence(): void
    {
        $recurringAccount = RecurringAccount::factory()->make([
            'frequency' => RecurrenceFrequency::Monthly,
            'starts_at' => '2026-01-10',
            'ends_at' => '2026-12-10',
        ]);

        $this->assertSame('2026-01-10', $recurringAccount->nextOccurrence(CarbonImmutable::parse('2025-06-01'))->toDateString());
        $this->assertSame('2026-11-10', $recurringAccount->nextOccurrence(CarbonImmutable::parse('2026-11-10'))->toDateString());
        $this->assertSame('2026-12-10', $recurringAccount->nextOccurrence(CarbonImmutable::parse('2026-11-11'))->toDateString());
        $this->assertNull($recurringAccount->nextOccurrence(CarbonImmutable::parse('2026-12-11')));
    }
}
