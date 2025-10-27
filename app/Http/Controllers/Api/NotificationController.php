<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;
use App\Models\Customer;

class NotificationController extends Controller
{
    /**
     * Get customer notifications
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();

        $notifications = Notification::where('customer_id', $customer->id)
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->unread_only, function ($query) {
                return $query->whereNull('read_at');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $customer = $request->user();

        $count = Notification::where('customer_id', $customer->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $customer = $request->user();

        $notification = Notification::where('customer_id', $customer->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue',
            'data' => $notification->fresh()
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $customer = $request->user();

        Notification::where('customer_id', $customer->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications marquées comme lues'
        ]);
    }

    /**
     * Delete notification
     */
    public function delete(Request $request, $id): JsonResponse
    {
        $customer = $request->user();

        $notification = Notification::where('customer_id', $customer->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée'
        ]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll(Request $request): JsonResponse
    {
        $customer = $request->user();

        Notification::where('customer_id', $customer->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications supprimées'
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $customer = $request->user();

        $request->validate([
            'order_updates' => 'boolean',
            'promotions' => 'boolean',
            'newsletter' => 'boolean',
            'push_notifications' => 'boolean',
            'email_notifications' => 'boolean',
        ]);

        $preferences = array_merge(
            $customer->notification_preferences ?? [],
            $request->only(['order_updates', 'promotions', 'newsletter', 'push_notifications', 'email_notifications'])
        );

        $customer->update(['notification_preferences' => $preferences]);

        return response()->json([
            'success' => true,
            'message' => 'Préférences de notifications mises à jour',
            'data' => $preferences
        ]);
    }

    /**
     * Get notification preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $customer = $request->user();

        $preferences = $customer->notification_preferences ?? [
            'order_updates' => true,
            'promotions' => true,
            'newsletter' => false,
            'push_notifications' => true,
            'email_notifications' => true,
        ];

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request): JsonResponse
    {
        $customer = $request->user();

        $notification = Notification::create([
            'customer_id' => $customer->id,
            'type' => 'test',
            'title' => 'Notification de test',
            'message' => 'Ceci est une notification de test pour vérifier que votre système fonctionne correctement.',
            'data' => [
                'test' => true,
                'timestamp' => now()->toISOString()
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification de test envoyée',
            'data' => $notification
        ]);
    }

    /**
     * Get notification statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $customer = $request->user();

        $stats = [
            'total' => Notification::where('customer_id', $customer->id)->count(),
            'unread' => Notification::where('customer_id', $customer->id)->whereNull('read_at')->count(),
            'by_type' => Notification::where('customer_id', $customer->id)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'recent' => Notification::where('customer_id', $customer->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
