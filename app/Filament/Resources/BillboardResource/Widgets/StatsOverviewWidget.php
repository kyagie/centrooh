<?php

namespace App\Filament\Resources\BillboardResource\Widgets;

use App\Models\Billboard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Review', Billboard::where('status', 'pending')->count())
                ->description('Billboards awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('In Review', Billboard::where('status', 'in_review')->count())
                ->description('Billboards being reviewed')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('info'),
                
            Stat::make('Updated', Billboard::where('status', 'updated')->count())
                ->description('Recently updated billboards')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('success'),
                
            Stat::make('Rejected', Billboard::where('status', 'rejected')->count())
                ->description('Rejected billboards')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
                
            Stat::make('Passed', Billboard::where('status', 'passed')->count())
                ->description('Approved billboards')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            
        ];
    }
    
    protected function getColumns(): int
    {
        return 3;
    }
}
