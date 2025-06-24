<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentNotification;
use App\Models\AgentNotificationType;
use App\Models\Billboard;
use App\Models\BillboardImage;

class AgentNotificationService
{
    /**
     * Send a notification to an agent about a billboard image review
     *
     * @param BillboardImage $billboardImage
     * @param string $status
     * @param string $reviewNote
     * @return AgentNotification|null
     */
    public function sendBillboardReviewNotification(BillboardImage $billboardImage, string $status, string $reviewNote): ?AgentNotification
    {
        if ($billboardImage->uploader_type !== 'agent' || !$billboardImage->agent_id) {
            return null;
        }

        $notificationType = $this->findOrCreateBillboardReviewType();

        if (!$notificationType) {
            return null;
        }

        return AgentNotification::create([
            'agent_id' => $billboardImage->agent_id,
            'agent_notification_type_id' => $notificationType->id,
            'title' => "Billboard Image Review - {$billboardImage->billboard?->name}",
            'body' => "The billboard image for '{$billboardImage->billboard?->name}' you recently uploaded has been reviewed and marked as {$status}" .
                ($reviewNote ? " with the following note: {$reviewNote}" : ''),
            'meta' => [
                'billboard_image_id' => $billboardImage->id,
                'billboard' => $billboardImage->billboard->name,
            ],
        ]);
    }

    /**
     * Find or create the BillboardReview notification type
     *
     * @return AgentNotificationType|null
     */
    private function findOrCreateBillboardReviewType(): ?AgentNotificationType
    {
        $notificationType = AgentNotificationType::where('slug', 'billboard-image-review')
            ->first();

        if (!$notificationType) {
            $notificationType = AgentNotificationType::create([
                'name' => 'Billboard Image Review',
                'slug' => 'billboard-image-review',
                'description' => 'Notification for billboard image review status updates',
            ]);
        }
        return $notificationType;
    }

    /**
     * Send a notification to an agent requesting an updated billboard photo
     * and update the billboard status to pending
     *
     * @param Billboard $billboard
     * @param string|null $requestNote
     * @param int|null $requestedBy
     * @return array
     */
    public function requestBillboardPhotoUpdate(Billboard $billboard, ?string $requestNote = null, ?int $requestedBy = null): array
    {
        // Validate that the billboard has assigned agents
        if ($billboard->agents->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No agents assigned to this billboard',
                'notifications_sent' => 0
            ];
        }

        $notificationType = $this->findOrCreateBillboardPhotoRequestType();

        if (!$notificationType) {
            return [
                'success' => false,
                'message' => 'Failed to create notification type',
                'notifications_sent' => 0
            ];
        }
        
        try {
            $billboard->update([
                'status' => 'pending'
            ]);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update billboard status: ' . $e->getMessage(),
                'notifications_sent' => 0
            ];
        }

        $notificationsSent = 0;
        $failedNotifications = 0;

        // Send notification to each agent assigned to this billboard
        foreach ($billboard->agents as $agent) {
            try {
                AgentNotification::create([
                    'agent_id' => $agent->id,
                    'agent_notification_type_id' => $notificationType->id,
                    'title' => "Photo Update Request - {$billboard->name}",
                    'body' => "Please provide an updated photo for billboard '{$billboard->name}'" .
                        ($requestNote ? ". Note: {$requestNote}" : ''),
                    'created_by' => $requestedBy,
                    'meta' => [
                        'billboard_id' => $billboard->id,
                        'billboard_name' => $billboard->name,
                        'request_type' => 'photo_update',
                        'previous_status' => $billboard->getOriginal('status'),
                        'new_status' => 'pending',
                    ],
                ]);
                
                $notificationsSent++;
            } catch (\Exception $e) {
                $failedNotifications++;
            }
        }

        return [
            'success' => $notificationsSent > 0,
            'message' => $notificationsSent . ' notification(s) sent successfully' . 
                         ($failedNotifications > 0 ? ", {$failedNotifications} failed" : '') .
                         '. Billboard status updated to pending.',
            'notifications_sent' => $notificationsSent,
            'status_updated' => true,
            'billboard_id' => $billboard->id,
            'billboard_name' => $billboard->name
        ];
    }

    /**
     * Find or create the BillboardPhotoRequest notification type
     *
     * @return AgentNotificationType|null
     */
    private function findOrCreateBillboardPhotoRequestType(): ?AgentNotificationType
    {
        $notificationType = AgentNotificationType::where('slug', 'billboard-photo-request')
            ->first();

        if (!$notificationType) {
            $notificationType = AgentNotificationType::create([
                'name' => 'Billboard Photo Request',
                'slug' => 'billboard-photo-request',
                'description' => 'Request for updating billboard photos',
                'icon' => 'heroicon-o-camera',
                'color' => 'primary',
                'status' => 'active',
            ]);
        }
        return $notificationType;
    }
}
