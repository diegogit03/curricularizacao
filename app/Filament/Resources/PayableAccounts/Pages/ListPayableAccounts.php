<?php

namespace App\Filament\Resources\PayableAccounts\Pages;

use App\Filament\Resources\PayableAccounts\PayableAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayableAccounts extends ListRecords
{
    protected static string $resource = PayableAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
