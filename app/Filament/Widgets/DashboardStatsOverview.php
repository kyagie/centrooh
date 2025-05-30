<?php

namespace App\Filament\Widgets;

use App\Models\Agent;
use App\Models\Billboard;
use App\Models\MediaOwner;
use App\Models\District;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Billboards', Billboard::count())
                ->description('All billboards in the system')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),
            Stat::make('Media Owners', MediaOwner::count())
                ->description('All media owners registered')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            // Stat::make('Active Billboards', Billboard::where('is_active', true)->count())
            //     ->description('Currently active billboards')
            //     ->descriptionIcon('heroicon-m-check-circle')
            //     ->color('success'),

            // Stat::make('Billboard Images', BillboardImage::count())
            //     ->description('Total uploaded images')
            //     ->descriptionIcon('heroicon-m-photo')
            //     ->color('info'),
            Stat::make('Active Agents', Agent::where('status', 'active')->count())
                ->description('Field agents')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            //Media Owners count
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
