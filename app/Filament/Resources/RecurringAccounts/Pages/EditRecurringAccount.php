<?php

namespace App\Filament\Resources\RecurringAccounts\Pages;

use App\Filament\Resources\RecurringAccounts\RecurringAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRecurringAccount extends EditRecord
{
    protected static string $resource = RecurringAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
