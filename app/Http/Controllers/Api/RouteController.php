<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\OptimizeDriverRouteJob;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RouteController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint is for drivers only.',
            ], 403);
        }

        $cached = Cache::get("driver_route_{$user->id}");

        if ($cached) {
            return response()->json([
                'success' => true,
                'source'  => 'cache',
                'data'    => $cached,
            ]);
        }

        $driverProfile = $user->driverProfile;

        $orders = Order::where('driver_profile_id', $driverProfile?->id)
            ->where('status', 'picked_up')
            ->whereNotNull('route_order')
            ->orderBy('route_order')
            ->with('receiver')
            ->get(['id', 'order_number', 'route_order', 'driver_profile_id']);

        return response()->json([
            'success' => true,
            'source'  => 'database',
            'data'    => [
                'driver_id'        => $user->id,
                'total_distance_m' => null,
                'total_duration_s' => null,
                'polyline'         => null,
                'optimized_at'     => null,
                'orders'           => $orders->map(fn ($o) => [
                    'id'           => $o->id,
                    'order_number' => $o->order_number,
                    'route_order'  => $o->route_order,
                    'latitude'     => $o->receiver?->receiver_latitude,
                    'longitude'    => $o->receiver?->receiver_longitude,
                    'receiver'     => $o->receiver?->receiver_name,
                    'address'      => $o->receiver?->address_text,
                ])->values(),
            ],
        ]);
    }

    public function recalculate(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->isDriver()) {
            $driverId = $user->id;
        } elseif ($user->isAdmin() || $user->isSuperAdmin()) {
            $request->validate([
                'driver_id' => ['required', 'integer', 'exists:users,id'],
            ]);
            $driverId = $request->integer('driver_id');
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        Cache::forget('laravel_unique_job:App\\Jobs\\OptimizeDriverRouteJob:driver_route_' . $driverId);
        Cache::forget("driver_route_{$driverId}");

        OptimizeDriverRouteJob::dispatch($driverId)->onQueue('default');

        return response()->json([
            'success' => true,
            'message' => 'Route recalculation queued.',
            'data'    => ['driver_id' => $driverId],
        ]);
    }
}
