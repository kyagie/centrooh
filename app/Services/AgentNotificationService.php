<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentNotification;
use App\Models\AgentNotificationType;
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
}
