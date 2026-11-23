<?php

namespace App\Filament\Resources\Accounts\Actions;

use App\Enums\TransactionType;
use App\Models\Account;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class MarkAsSettledAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'markAsSettled';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn (Account $record): string => $record->type === TransactionType::Revenue ? 'Marcar como recebida' : 'Marcar como paga')
            ->icon(Heroicon::CheckCircle)
            ->color('success')
            ->visible(fn (Account $record): bool => ! $record->isSettled() && ! $record->trashed())
            ->modalHeading(fn (Account $record): string => $record->type === TransactionType::Revenue ? 'Marcar conta como recebida' : 'Marcar conta como paga')
            ->modalSubmitActionLabel('Confirmar')
            ->schema([
                DatePicker::make('settled_at')
                    ->label(fn (Account $record): string => $record->type === TransactionType::Revenue ? 'Data de recebimento' : 'Data de pagamento')
                    ->default(now())
                    ->required(),
            ])
            ->successNotificationTitle(fn (Account $record): string => $record->type === TransactionType::Revenue ? 'Conta marcada como recebida' : 'Conta marcada como paga')
            ->action(function (Account $record, array $data): void {
                $record->markAsSettled(Carbon::parse($data['settled_at']));

                $this->success();
            });
    }
}
