<?php

namespace App\Filament\Resources\AgentNotificationResource\Pages;

use App\Filament\Resources\AgentNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAgentNotification extends ViewRecord
{
    protected static string $resource = AgentNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->url(fn () => static::getResource()::getUrl())
                ->color('gray'),
        ];
    }
}
