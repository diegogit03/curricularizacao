<?php

namespace App\Filament\Resources\PayableAccounts;

use App\Filament\Resources\PayableAccounts\Pages\CreatePayableAccount;
use App\Filament\Resources\PayableAccounts\Pages\EditPayableAccount;
use App\Filament\Resources\PayableAccounts\Pages\ListPayableAccounts;
use App\Filament\Resources\PayableAccounts\Schemas\PayableAccountForm;
use App\Filament\Resources\PayableAccounts\Tables\PayableAccountsTable;
use App\Models\PayableAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayableAccountResource extends Resource
{
    protected static ?string $model = PayableAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowTrendingDown;

    protected static ?string $label = 'Conta a Pagar';

    protected static ?string $pluralLabel = 'Contas a Pagar';

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return PayableAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayableAccountsTable::configure($table);
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
            'index' => ListPayableAccounts::route('/'),
            'create' => CreatePayableAccount::route('/create'),
            'edit' => EditPayableAccount::route('/{record}/edit'),
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
