<?php

namespace App\Filament\Resources\AgentNotificationTypeResource\Pages;

use App\Filament\Resources\AgentNotificationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgentNotificationType extends EditRecord
{
    protected static string $resource = AgentNotificationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
