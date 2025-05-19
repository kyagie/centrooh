<?php

namespace App\Filament\Resources\AgentNotificationResource\Pages;

use App\Filament\Resources\AgentNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgentNotifications extends ListRecords
{
    protected static string $resource = AgentNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
