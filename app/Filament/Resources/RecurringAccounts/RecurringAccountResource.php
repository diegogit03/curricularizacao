<?php

namespace App\Filament\Resources\RecurringAccounts;

use App\Filament\Resources\RecurringAccounts\Pages\CreateRecurringAccount;
use App\Filament\Resources\RecurringAccounts\Pages\EditRecurringAccount;
use App\Filament\Resources\RecurringAccounts\Pages\ListRecurringAccounts;
use App\Filament\Resources\RecurringAccounts\Schemas\RecurringAccountForm;
use App\Filament\Resources\RecurringAccounts\Tables\RecurringAccountsTable;
use App\Models\RecurringAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecurringAccountResource extends Resource
{
    protected static ?string $model = RecurringAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static ?string $label = 'Conta Recorrente';

    protected static ?string $pluralLabel = 'Contas Recorrentes';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return RecurringAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecurringAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecurringAccounts::route('/'),
            'create' => CreateRecurringAccount::route('/create'),
            'edit' => EditRecurringAccount::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
