<?php

namespace App\Filament\Resources\Accounts\Schemas;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AccountForm
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
                    ->required()
                    ->live(),
                Select::make('status')
                    ->label('Status')
                    ->options(AccountStatus::class)
                    ->default(AccountStatus::Pending)
                    ->required()
                    ->live(),
                TextInput::make('value')
                    ->label('Valor')
                    ->required()
                    ->numeric(),
                DatePicker::make('due_at')
                    ->label('Vencimento')
                    ->required(),
                DatePicker::make('settled_at')
                    ->label(fn (Get $get): string => self::isRevenue($get('type')) ? 'Data de recebimento' : 'Data de pagamento')
                    ->visible(fn (Get $get): bool => self::isSettled($get('status')))
                    ->required(fn (Get $get): bool => self::isSettled($get('status'))),
                Select::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->required(),
                Select::make('bank_account_id')
                    ->label('Conta bancária')
                    ->relationship('bankAccount', 'name'),
            ]);
    }

    private static function isRevenue(TransactionType|string|null $type): bool
    {
        return $type === TransactionType::Revenue || $type === TransactionType::Revenue->value;
    }

    private static function isSettled(AccountStatus|string|null $status): bool
    {
        return $status === AccountStatus::Settled || $status === AccountStatus::Settled->value;
    }
}
