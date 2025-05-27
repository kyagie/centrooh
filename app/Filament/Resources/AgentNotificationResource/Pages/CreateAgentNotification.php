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

        $lastNotification = null;
        foreach ($agentIds as $agentId) {
            $notificationData = $data;
            $notificationData['agent_id'] = $agentId;
            $lastNotification = AgentNotification::create($notificationData);
        }
        return $lastNotification;
    }
}
