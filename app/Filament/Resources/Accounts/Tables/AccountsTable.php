<?php

namespace App\Filament\Resources\Accounts\Tables;

use App\Enums\AccountStatus;
use App\Enums\TransactionType;
use App\Filament\Resources\Accounts\Actions\MarkAsSettledAction;
use App\Models\Account;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('due_at')
            ->columns([
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (TransactionType $state): string => $state === TransactionType::Revenue ? 'A receber' : 'A pagar')
                    ->color(fn (TransactionType $state): string => $state === TransactionType::Revenue ? 'success' : 'danger'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AccountStatus $state, Account $record): string => $state->labelFor($record->type)),
                TextColumn::make('value')
                    ->label('Valor')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('due_at')
                    ->label('Vencimento')
                    ->date()
                    ->sortable(),
                TextColumn::make('settled_at')
                    ->label('Pago/recebido em')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bankAccount.name')
                    ->label('Conta bancária')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->label('Excluído em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(AccountStatus::class),
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->relationship('category', 'name'),
                SelectFilter::make('bankAccount')
                    ->label('Conta bancária')
                    ->relationship('bankAccount', 'name'),
            ])
            ->recordActions([
                MarkAsSettledAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
