<?php

namespace App\Filament\Resources\ReceivableAccounts\Pages;

use App\Filament\Resources\ReceivableAccounts\ReceivableAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReceivableAccounts extends ListRecords
{
    protected static string $resource = ReceivableAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
