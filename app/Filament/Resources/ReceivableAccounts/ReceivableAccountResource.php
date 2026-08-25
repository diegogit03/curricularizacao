<?php

namespace App\Filament\Resources\ReceivableAccounts;

use App\Filament\Resources\ReceivableAccounts\Pages\CreateReceivableAccount;
use App\Filament\Resources\ReceivableAccounts\Pages\EditReceivableAccount;
use App\Filament\Resources\ReceivableAccounts\Pages\ListReceivableAccounts;
use App\Filament\Resources\ReceivableAccounts\Schemas\ReceivableAccountForm;
use App\Filament\Resources\ReceivableAccounts\Tables\ReceivableAccountsTable;
use App\Models\ReceivableAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReceivableAccountResource extends Resource
{
    protected static ?string $model = ReceivableAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowTrendingUp;

    protected static ?string $label = 'Conta a Receber';

    protected static ?string $pluralLabel = 'Contas a Receber';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return ReceivableAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceivableAccountsTable::configure($table);
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
            'index' => ListReceivableAccounts::route('/'),
            'create' => CreateReceivableAccount::route('/create'),
            'edit' => EditReceivableAccount::route('/{record}/edit'),
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
