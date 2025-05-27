<?php

namespace App\Filament\Resources\AgentResource\Pages;

use App\Filament\Resources\AgentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\AgentNotification;
use App\Models\AgentNotificationType;
use Illuminate\Database\Eloquent\Model;

class EditAgent extends EditRecord
{
    protected static string $resource = AgentResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $originalStatus = $record->status;
        $record->update($data);
        $newStatus = $record->status;

        if ($originalStatus !== $newStatus) {
            // Get or create the notification type for profile update
            $notificationType = AgentNotificationType::firstOrCreate(
                ['slug' => 'profile-update'],
                [
                    'name' => 'Profile Update',
                    'description' => 'Notification for agent profile updates',
                ]
            );

            AgentNotification::firstOrCreate([
                'agent_id' => $record->id,
                'agent_notification_type_id' => $notificationType->id,
                'title' => 'Status Updated',
                'body' => 'Your profile status has been changed to: ' . ucfirst($newStatus),
            ]);
        }

        return $record;
    }
}
