<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationsApiController extends Controller
{
    /**
     * Display a listing of notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::query();

        // Filter by user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by customer_id
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by read status
        if ($request->has('read')) {
            if ($request->read === '1' || $request->read === 'true') {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($notifications);
    }

    /**
     * Store a new notification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|integer|exists:users,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'action_url' => 'nullable|string|url|max:255',
            'priority' => 'sometimes|in:low,medium,high',
        ]);

        $notification = Notification::create($validated);

        return response()->json([
            'message' => 'Notification created successfully',
            'notification' => $notification
        ], 201);
    }

    /**
     * Display the specified notification
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);

        return response()->json($notification);
    }

    /**
     * Mark notification as read
     *
     * @param int $id
     * @return JsonResponse
     */
    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);

        $notification->update([
            'read_at' => now()
        ]);

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required_without:customer_id|integer|exists:users,id',
            'customer_id' => 'required_without:user_id|integer|exists:users,id',
        ]);

        $query = Notification::whereNull('read_at');

        if (isset($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        } else {
            $query->where('customer_id', $validated['customer_id']);
        }

        $updated = $query->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read',
            'count' => $updated
        ]);
    }

    /**
     * Remove the specified notification
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Get unread notifications count
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required_without:customer_id|integer|exists:users,id',
            'customer_id' => 'required_without:user_id|integer|exists:users,id',
        ]);

        $query = Notification::whereNull('read_at');

        if (isset($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        } else {
            $query->where('customer_id', $validated['customer_id']);
        }

        $count = $query->count();

        return response()->json([
            'unread_count' => $count
        ]);
    }
}
