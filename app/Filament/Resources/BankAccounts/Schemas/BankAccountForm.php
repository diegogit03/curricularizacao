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
                    ->label('Nome')
                    ->required(),
                TextInput::make('bank')
                    ->label('Banco')
                    ->required(),
                TextInput::make('agency')
                    ->label('Agência'),
                TextInput::make('account_number')
                    ->label('Número da conta'),
                TextInput::make('initial_balance')
                    ->label('Saldo inicial')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
