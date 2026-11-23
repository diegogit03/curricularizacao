<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('bank')
                    ->required(),
                TextInput::make('agency'),
                TextInput::make('account_number'),
                TextInput::make('initial_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
