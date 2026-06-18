<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SystemNotificationResource;
use App\Models\SystemNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = $user->systemNotifications()->latest();

        if ($request->filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(20);

        return response()->json([
            'success'      => true,
            'unread_count' => $user->systemNotifications()->whereNull('read_at')->count(),
            'data'         => SystemNotificationResource::collection($notifications->items()),
            'meta'         => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notification = $user->systemNotifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
                'code'    => 'NOTIFICATION_NOT_FOUND',
            ], 404);
        }

        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => Carbon::now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data'    => new SystemNotificationResource($notification),
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $updated = $user->systemNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data'    => ['marked_count' => $updated],
        ]);
    }
}
