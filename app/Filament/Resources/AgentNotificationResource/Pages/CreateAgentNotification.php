<?php

namespace App\Filament\Resources\AgentNotificationResource\Pages;

use App\Filament\Resources\AgentNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Agent;
use App\Models\AgentNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateAgentNotification extends CreateRecord
{
    protected static string $resource = AgentNotificationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $agentIds = Arr::wrap($data['agent_ids'] ?? []);
        unset($data['agent_ids']);

        if (in_array('all', $agentIds)) {
            $agentIds = Agent::pluck('id')->all();
        }

        // Prepare batch data
        $notificationsToInsert = [];
        foreach ($agentIds as $agentId) {
            $notificationsToInsert[] = array_merge($data, [
            'agent_id' => $agentId,
            'created_at' => now(),
            'updated_at' => now(),
            ]);
        }

        // Use chunk insert to improve performance
        $chunkSize = 100;
        $lastInsertedId = null;
        
        foreach (array_chunk($notificationsToInsert, $chunkSize) as $chunk) {
            AgentNotification::insert($chunk);
            if (!$lastInsertedId) {
            $lastInsertedId = AgentNotification::latest('id')->first()->id;
            }
        }

        // Return the last created notification
        return AgentNotification::find($lastInsertedId);
    }
}
