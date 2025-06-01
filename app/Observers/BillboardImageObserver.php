<?php

namespace App\Observers;

use App\Models\BillboardImage;

class BillboardImageObserver
{
    /**
     * Handle the BillboardImage "created" event.
     */
    public function created(BillboardImage $billboardImage): void
    {
        // When a billboard image is created, we'll update the billboard status
        // if the new status requires it
        $this->updateBillboardStatus($billboardImage);
    }

    /**
     * Handle the BillboardImage "updated" event.
     */
    public function updated(BillboardImage $billboardImage): void
    {
        // If the status changed, update the parent billboard status
        if ($billboardImage->wasChanged('status')) {
            $this->updateBillboardStatus($billboardImage);
        }
    }

    /**
     * Update the parent billboard status based on the billboard image status
     */
    private function updateBillboardStatus(BillboardImage $billboardImage): void
    {
        // Make sure there's a billboard associated with this image
        if (!$billboardImage->billboard) {
            return;
        }

        $billboard = $billboardImage->billboard;
        
        // Get the current status of the billboard image
        $status = $billboardImage->status;

        // Update billboard status based on image status
        switch ($status) {
            case 'passed':
                $billboard->status = 'passed';
                break;
            case 'rejected':
                $billboard->status = 'rejected';
                break;
            case 'in_review':
                $billboard->status = 'in_review';
                break;
            case 'pending':
                // Only update to pending if the billboard isn't already in a more "active" state
                if (!in_array($billboard->status, ['in_review', 'passed', 'updated'])) {
                    $billboard->status = 'pending';
                }
                break;
        }

        // Save the billboard with the updated status
        $billboard->save();
    }

    /**
     * Handle the BillboardImage "deleted" event.
     */
    public function deleted(BillboardImage $billboardImage): void
    {
        // No action needed for now
    }

    /**
     * Handle the BillboardImage "restored" event.
     */
    public function restored(BillboardImage $billboardImage): void
    {
        // No action needed for now
    }

    /**
     * Handle the BillboardImage "force deleted" event.
     */
    public function forceDeleted(BillboardImage $billboardImage): void
    {
        // No action needed for now
    }
}
