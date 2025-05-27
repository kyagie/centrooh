<?php

namespace App\Observers;

use App\Models\AgentNotification;
use App\Models\AgentNotificationType;
use App\Models\Billboard;

class BillboardObserver
{
    /**
     * Handle the Billboard "created" event.
     */
    public function created(Billboard $billboard): void
    {
        // Notify if agent_id is set on creation
        if ($billboard->agent_id) {
            $this->notifyAgentAssignment($billboard);
        }
    }

    /**
     * Handle the Billboard "updated" event.
     */
    public function updated(Billboard $billboard): void
    {
        // Only notify if agent_id is set and has changed
        if ($billboard->wasChanged('agent_id') && $billboard->agent_id) {
            $this->notifyAgentAssignment($billboard);
        }
    }

    /**
     * Notify agent of billboard assignment
     */
    private function notifyAgentAssignment(Billboard $billboard): void
    {
        $notificationType = AgentNotificationType::firstOrCreate(
            ['slug' => 'new-billboard-assignment'],
            [
                'name' => 'New Billboard Assignment',
                'description' => 'Notification for new billboard assignment',
            ]
        );

        AgentNotification::create([
            'agent_id' => $billboard->agent_id,
            'agent_notification_type_id' => $notificationType->id,
            'title' => 'You have been assigned a new billboard.',
            'body' => 'A new billboard has been assigned to you: ' . $billboard->name,
            'status' => 'unread',
            'meta' => [
                'billboard_id' => $billboard->id,
                'billboard_name' => $billboard->name,
            ],
        ]);
    }

    /**
     * Handle the Billboard "deleted" event.
     */
    public function deleted(Billboard $billboard): void
    {
        //
    }

    /**
     * Handle the Billboard "restored" event.
     */
    public function restored(Billboard $billboard): void
    {
        //
    }

    /**
     * Handle the Billboard "force deleted" event.
     */
    public function forceDeleted(Billboard $billboard): void
    {
        //
    }
}
