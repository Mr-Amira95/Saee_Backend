<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'platform' => ['required', 'in:ios,android'],
        ]);

        // Upsert device — reassigns token if it previously belonged to another user
        $device = UserDevice::updateOrCreate(
            ['fcm_token' => $request->token],
            [
                'user_id'  => $request->user()->id,
                'platform' => $request->platform,
            ]
        );

        return response()->json([
            'success'               => true,
            'message'               => __('Device token registered'),
            'notifications_enabled' => $device->notifications_enabled,
        ]);
    }
}
