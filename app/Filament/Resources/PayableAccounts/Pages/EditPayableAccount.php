<?php

namespace App\Filament\Resources\PayableAccounts\Pages;

use App\Filament\Resources\PayableAccounts\PayableAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPayableAccount extends EditRecord
{
    protected static string $resource = PayableAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
