<?php

namespace App\Filament\Resources\PayableAccounts\Schemas;

use App\Enums\PayableAccountStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayableAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->required(),
                Select::make('status')
                    ->options(PayableAccountStatus::class)
                    ->required(),
                TextInput::make('value')
                    ->required()
                    ->numeric(),
                DatePicker::make('due_at')
                    ->required(),
                DatePicker::make('paid_at')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
            ]);
    }
}
