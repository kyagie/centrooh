<?php

namespace App\Filament\Resources\AgentNotificationResource\Pages;

use App\Filament\Resources\AgentNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgentNotification extends EditRecord
{
    protected static string $resource = AgentNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
