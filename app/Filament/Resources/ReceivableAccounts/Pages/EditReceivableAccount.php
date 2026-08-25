<?php

namespace App\Filament\Resources\ReceivableAccounts\Pages;

use App\Filament\Resources\ReceivableAccounts\ReceivableAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditReceivableAccount extends EditRecord
{
    protected static string $resource = ReceivableAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
