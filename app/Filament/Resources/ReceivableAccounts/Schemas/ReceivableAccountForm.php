<?php

namespace App\Filament\Resources\ReceivableAccounts\Schemas;

use App\Enums\ReceivableAccountStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReceivableAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->required(),
                Select::make('status')
                    ->options(ReceivableAccountStatus::class)
                    ->required(),
                TextInput::make('value')
                    ->required()
                    ->numeric(),
                DatePicker::make('due_at')
                    ->required(),
                DatePicker::make('received_at')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
            ]);
    }
}
