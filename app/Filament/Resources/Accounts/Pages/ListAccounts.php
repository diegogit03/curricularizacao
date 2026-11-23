<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\Accounts\AccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAccounts extends ListRecords
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'payable' => Tab::make('A pagar')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', TransactionType::Expense)),
            'receivable' => Tab::make('A receber')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', TransactionType::Revenue)),
        ];
    }
}
