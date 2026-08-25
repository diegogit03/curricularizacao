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
                    ->required(),
                Select::make('type')
                    ->options(TransactionType::class)
                    ->required(),
                TextInput::make('value')
                    ->required()
                    ->numeric(),
                DatePicker::make('date')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
            ]);
    }
}
