<?php

namespace App\Console\Commands;

use App\Enums\AccountStatus;
use App\Models\Account;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('accounts:mark-overdue')]
#[Description('Marca como vencidas as contas pendentes cujo vencimento já passou')]
class MarkOverdueAccounts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = Account::query()
            ->pastDue()
            ->update(['status' => AccountStatus::Overdue]);

        $this->components->info("{$updated} conta(s) marcada(s) como vencida(s).");

        return self::SUCCESS;
    }
}
