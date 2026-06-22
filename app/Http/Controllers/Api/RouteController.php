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
    /**
     * GET /driver/route
     *
     * Returns the optimized delivery sequence for the authenticated driver.
     * Reads from cache (populated by OptimizeDriverRouteJob) when fresh,
     * falls back to DB route_order column when cache has expired.
     */
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

        // Cache miss — read from DB sorted by route_order
        $orders = Order::where('driver_id', $user->id)
            ->where('status', 'picked_up')
            ->whereNotNull('route_order')
            ->orderBy('route_order')
            ->get(['id', 'order_number', 'route_order', 'receiver_latitude',
                   'receiver_longitude', 'receiver_name', 'address_text']);

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
                    'latitude'     => $o->receiver_latitude,
                    'longitude'    => $o->receiver_longitude,
                    'receiver'     => $o->receiver_name,
                    'address'      => $o->address_text,
                ])->values(),
            ],
        ]);
    }

    /**
     * POST /driver/route/recalculate
     *
     * Forces immediate re-optimization bypassing the debounce lock.
     * Drivers can recalculate their own route; admins can specify any driver.
     */
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

        // Delete the ShouldBeUnique lock so the next dispatch is not silently dropped
        Cache::forget('laravel_unique_job:App\\Jobs\\OptimizeDriverRouteJob:driver_route_' . $driverId);

        // Bust the cached result too
        Cache::forget("driver_route_{$driverId}");

        // Dispatch immediately — no delay for a forced recalculation
        OptimizeDriverRouteJob::dispatch($driverId)->onQueue('default');

        return response()->json([
            'success' => true,
            'message' => 'Route recalculation queued.',
            'data'    => ['driver_id' => $driverId],
        ]);
    }
}
