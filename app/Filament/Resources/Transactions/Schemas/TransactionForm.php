<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->label('Descrição')
                    ->required(),
                Select::make('type')
                    ->label('Tipo')
                    ->options(TransactionType::class)
                    ->required(),
                TextInput::make('value')
                    ->label('Valor')
                    ->required()
                    ->numeric(),
                DatePicker::make('date')
                    ->label('Data')
                    ->required(),
                Select::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('bank_account_id')
                    ->label('Conta bancária')
                    ->relationship('bankAccount', 'name'),
            ]);
    }
}
