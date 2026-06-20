<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token'             => ['required', 'string'],
            'notifications_enabled' => ['required', 'boolean'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $device = UserDevice::where('fcm_token', $request->fcm_token)
            ->where('user_id', $user->id)
            ->first();

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
                'code'    => 'DEVICE_NOT_FOUND',
            ], 404);
        }

        $device->update(['notifications_enabled' => $request->boolean('notifications_enabled')]);

        return response()->json([
            'success'               => true,
            'message'               => $device->notifications_enabled
                ? 'Notifications enabled for this device'
                : 'Notifications disabled for this device',
            'notifications_enabled' => $device->notifications_enabled,
        ]);
    }
}
