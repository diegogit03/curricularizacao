<?php

namespace Tests\Feature;

use App\Console\Commands\MarkOverdueAccounts;
use App\Enums\AccountStatus;
use App\Models\Account;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkOverdueAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_accounts_past_due_are_marked_as_overdue(): void
    {
        $this->travelTo('2026-11-23 08:00:00');

        $pastDue = Account::factory()->create(['due_at' => '2026-11-22']);
        $dueToday = Account::factory()->create(['due_at' => '2026-11-23']);
        $future = Account::factory()->create(['due_at' => '2026-12-01']);

        $this->artisan('accounts:mark-overdue')
            ->expectsOutputToContain('1 conta(s) marcada(s) como vencida(s).')
            ->assertSuccessful();

        $this->assertSame(AccountStatus::Overdue, $pastDue->refresh()->status);
        $this->assertSame(AccountStatus::Pending, $dueToday->refresh()->status);
        $this->assertSame(AccountStatus::Pending, $future->refresh()->status);
    }

    public function test_settled_accounts_are_not_marked_as_overdue(): void
    {
        $this->travelTo('2026-11-23');

        $settled = Account::factory()->settled()->create(['due_at' => '2026-11-01']);

        $this->artisan('accounts:mark-overdue')->assertSuccessful();

        $this->assertSame(AccountStatus::Settled, $settled->refresh()->status);
    }

    public function test_trashed_accounts_are_ignored(): void
    {
        $this->travelTo('2026-11-23');

        $trashed = Account::factory()->create(['due_at' => '2026-11-01']);
        $trashed->delete();

        $this->artisan('accounts:mark-overdue')
            ->expectsOutputToContain('0 conta(s)')
            ->assertSuccessful();

        $this->assertSame(AccountStatus::Pending, Account::withTrashed()->find($trashed->id)->status);
    }

    public function test_command_is_scheduled_daily(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn (Event $event): bool => str_contains($event->command ?? '', 'accounts:mark-overdue'));

        $this->assertNotNull($event, 'O comando '.MarkOverdueAccounts::class.' não está agendado.');
        $this->assertSame('0 0 * * *', $event->expression);
    }
}
