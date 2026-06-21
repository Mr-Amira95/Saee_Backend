<?php

namespace App\Http\Controllers\Api;

use App\Events\DriverLocationUpdated;
use App\Http\Controllers\Controller;
use App\Models\DriverLocationHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed'     => 'nullable|numeric|min:0',
            'heading'   => 'nullable|numeric|between:0,360',
            'accuracy'  => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();

        if (! $user->driverProfile) {
            return response()->json(['message' => 'Not a driver account.'], 403);
        }

        $profile = $user->driverProfile;

        $profile->update([
            'current_latitude'   => $data['latitude'],
            'current_longitude'  => $data['longitude'],
            'location_updated_at' => now(),
        ]);

        DriverLocationHistory::create([
            'driver_profile_id' => $profile->id,
            'latitude'          => $data['latitude'],
            'longitude'         => $data['longitude'],
            'recorded_at'       => now(),
            'speed'             => $data['speed']   ?? null,
            'heading'           => $data['heading'] ?? null,
            'accuracy'          => $data['accuracy'] ?? null,
        ]);

        event(new DriverLocationUpdated(
            driverProfileId: $profile->id,
            driverName:      $user->name,
            latitude:        (float) $data['latitude'],
            longitude:       (float) $data['longitude'],
            updatedAt:       now()->toISOString(),
        ));

        return response()->json(['ok' => true]);
    }
}
