<?php

namespace App\Filament\Resources\RecurringAccounts\Tables;

use App\Enums\RecurrenceFrequency;
use App\Enums\TransactionType;
use App\Models\RecurringAccount;
use Carbon\CarbonImmutable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RecurringAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (TransactionType $state): string => $state === TransactionType::Revenue ? 'A receber' : 'A pagar')
                    ->color(fn (TransactionType $state): string => $state === TransactionType::Revenue ? 'success' : 'danger'),
                TextColumn::make('frequency')
                    ->label('Frequência')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('value')
                    ->label('Valor')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('next_occurrence')
                    ->label('Próximo vencimento')
                    ->state(fn (RecurringAccount $record): ?CarbonImmutable => $record->nextOccurrence())
                    ->date()
                    ->placeholder('-'),
                TextColumn::make('starts_at')
                    ->label('Primeiro vencimento')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label('Último vencimento')
                    ->date()
                    ->sortable()
                    ->placeholder('Sem fim'),
                ToggleColumn::make('is_active')
                    ->label('Ativa'),
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
                TernaryFilter::make('is_active')
                    ->label('Ativa'),
                SelectFilter::make('frequency')
                    ->label('Frequência')
                    ->options(RecurrenceFrequency::class),
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->relationship('category', 'name'),
                SelectFilter::make('bankAccount')
                    ->label('Conta bancária')
                    ->relationship('bankAccount', 'name'),
            ])
            ->recordActions([
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
