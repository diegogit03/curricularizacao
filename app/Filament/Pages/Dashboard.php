<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TransactionsChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;

class Dashboard extends BaseDashboard
{
    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            TransactionsChart::class,
        ];
    }
}
