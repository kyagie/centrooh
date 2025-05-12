<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the agent's notifications.
     */
    public function all(Request $request): JsonResponse
    {
        $agent = $this->getAuthenticatedAgent();

        $limit = $request->query('limit', 15);
        $notifications = $agent->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Display a listing of the agent's unread notifications.
     */
    public function unread(Request $request): JsonResponse
    {
        $agent = $this->getAuthenticatedAgent();

        $limit = $request->query('limit', 15);
        $notifications = $agent->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        $agent = $this->getAuthenticatedAgent();

        $notification = $agent->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $agent = $this->getAuthenticatedAgent();

        $agent->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Get count of unread notifications.
     */
    public function getUnreadCount(): JsonResponse
    {
        $agent = $this->getAuthenticatedAgent();

        $count = $agent->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Get the authenticated agent.
     */
    private function getAuthenticatedAgent()
    {
        return auth()->user()->agent;
    }
}
