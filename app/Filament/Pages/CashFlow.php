<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CashFlowChart;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class CashFlow extends BaseDashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'cash-flow';

    protected static ?string $title = 'Fluxo de Caixa';

    protected static ?string $navigationLabel = 'Fluxo de Caixa';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PresentationChartLine;

    protected static ?int $navigationSort = -1;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Data inicial')
                            ->default(now()->startOfMonth())
                            ->maxDate(fn (Get $get): ?string => $get('endDate'))
                            ->required(),
                        DatePicker::make('endDate')
                            ->label('Data final')
                            ->default(now()->startOfMonth()->addMonthsNoOverflow(5)->endOfMonth())
                            ->minDate(fn (Get $get): ?string => $get('startDate'))
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            CashFlowChart::class,
        ];
    }

    /**
     * @return int|array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return 1;
    }
}
