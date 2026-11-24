<?php

namespace App\Filament\Resources\RecurringAccounts\Schemas;

use App\Enums\RecurrenceFrequency;
use App\Enums\TransactionType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RecurringAccountForm
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
                    ->options([
                        TransactionType::Expense->value => 'A pagar',
                        TransactionType::Revenue->value => 'A receber',
                    ])
                    ->required(),
                TextInput::make('value')
                    ->label('Valor')
                    ->required()
                    ->numeric()
                    ->minValue(0.01),
                Select::make('frequency')
                    ->label('Frequência')
                    ->options(RecurrenceFrequency::class)
                    ->default(RecurrenceFrequency::Monthly)
                    ->required(),
                DatePicker::make('starts_at')
                    ->label('Primeiro vencimento')
                    ->required(),
                DatePicker::make('ends_at')
                    ->label('Último vencimento')
                    ->helperText('Deixe em branco para repetir indefinidamente.')
                    ->afterOrEqual('starts_at'),
                Select::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('bank_account_id')
                    ->label('Conta bancária')
                    ->relationship('bankAccount', 'name'),
                Toggle::make('is_active')
                    ->label('Ativa')
                    ->helperText('Contas inativas não entram na projeção do fluxo de caixa.')
                    ->default(true),
            ]);
    }
}
