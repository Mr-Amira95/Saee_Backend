<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'notifications_enabled' => ['required', 'boolean'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->update(['notifications_enabled' => $request->boolean('notifications_enabled')]);

        return response()->json([
            'success' => true,
            'message' => $user->notifications_enabled
                ? 'Notifications enabled successfully'
                : 'Notifications disabled successfully',
            'data'    => new UserResource($user),
        ]);
    }
}
