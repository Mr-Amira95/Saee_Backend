<?php

namespace App\Services;

use App\Models\DriverProfile;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteOptimizationService
{
    private const CHUNK_SIZE = 23;

    public function optimize(int $driverId): array
    {
        $profile = DriverProfile::where('user_id', $driverId)->first();

        if (! $profile || ! $profile->current_latitude || ! $profile->current_longitude) {
            return $this->emptyResult($driverId);
        }

        $orders = Order::where('driver_id', $driverId)
            ->where('status', 'picked_up')
            ->whereNotNull('receiver_latitude')
            ->whereNotNull('receiver_longitude')
            ->get();

        if ($orders->isEmpty()) {
            return $this->emptyResult($driverId);
        }

        $origin = "{$profile->current_latitude},{$profile->current_longitude}";

        // Trivial cases — no Google API call needed
        if ($orders->count() === 1) {
            $orders->first()->route_order = 1;
            $this->persistRouteOrder($orders);
            return $this->buildResult($orders, 0, 0, null, 0);
        }

        if ($orders->count() === 2) {
            $orders->values()->each(fn ($o, $i) => $o->route_order = $i + 1);
            $this->persistRouteOrder($orders);
            return $this->buildResult($orders, 0, 0, null, 0);
        }

        if ($orders->count() <= self::CHUNK_SIZE) {
            return $this->optimizeSingleBatch($orders, $origin);
        }

        return $this->optimizeInBatches($orders, $origin);
    }

    private function optimizeSingleBatch(Collection $orders, string $origin): array
    {
        $waypoints = $orders
            ->map(fn ($o) => "{$o->receiver_latitude},{$o->receiver_longitude}")
            ->toArray();

        $params   = $this->buildDirectionsRequest($origin, $origin, $waypoints);
        $response = $this->callDirectionsApi($params);
        $route    = $response['routes'][0];

        $waypointOrder = $route['waypoint_order'];
        $totalDistance = collect($route['legs'])->sum('distance.value');
        $totalDuration = collect($route['legs'])->sum(
            fn ($l) => $l['duration_in_traffic']['value'] ?? $l['duration']['value']
        );
        $polyline = $route['overview_polyline']['points'];

        $ordered = $this->applyWaypointOrder($orders, $waypointOrder);
        $this->persistRouteOrder($ordered);

        return $this->buildResult($ordered, $totalDistance, $totalDuration, $polyline, 1);
    }

    private function optimizeInBatches(Collection $orders, string $driverOrigin): array
    {
        $chunks        = $orders->chunk(self::CHUNK_SIZE);
        $totalChunks   = $chunks->count();
        $currentOrigin = $driverOrigin;
        $allOrdered    = collect();
        $totalDistance = 0;
        $totalDuration = 0;
        $lastPolyline  = null;

        foreach ($chunks as $chunkIndex => $chunk) {
            $isLastChunk = ($chunkIndex === $totalChunks - 1);

            $waypoints = $chunk
                ->map(fn ($o) => "{$o->receiver_latitude},{$o->receiver_longitude}")
                ->toArray();

            $params   = $this->buildDirectionsRequest($currentOrigin, $driverOrigin, $waypoints);
            $response = $this->callDirectionsApi($params);
            $route    = $response['routes'][0];

            $waypointOrder = $route['waypoint_order'];
            $legs          = $route['legs'];

            // For intermediate chunks, exclude the final "return-to-origin" leg
            // so we don't double-count the travel back between chunks.
            $countedLegs = $isLastChunk
                ? $legs
                : array_slice($legs, 0, count($waypoints));

            $totalDistance += collect($countedLegs)->sum('distance.value');
            $totalDuration += collect($countedLegs)->sum(
                fn ($l) => $l['duration_in_traffic']['value'] ?? $l['duration']['value']
            );

            if ($isLastChunk) {
                $lastPolyline = $route['overview_polyline']['points'];
            }

            $offset  = $allOrdered->count();
            $ordered = $this->applyWaypointOrder($chunk, $waypointOrder, $offset);
            $allOrdered = $allOrdered->merge($ordered);

            // The last stop of this chunk's optimized order becomes the next origin
            $lastStopIndex = end($waypointOrder);
            $lastOrder     = $chunk->values()->get($lastStopIndex);
            $currentOrigin = "{$lastOrder->receiver_latitude},{$lastOrder->receiver_longitude}";
        }

        $this->persistRouteOrder($allOrdered);

        return $this->buildResult($allOrdered, $totalDistance, $totalDuration, $lastPolyline, $totalChunks);
    }

    private function buildDirectionsRequest(
        string $origin,
        string $destination,
        array $waypoints
    ): array {
        $waypointStr = 'optimize:true|' . implode('|', $waypoints);

        return [
            'origin'         => $origin,
            'destination'    => $destination,
            'waypoints'      => $waypointStr,
            'mode'           => 'driving',
            'departure_time' => 'now',
            'traffic_model'  => 'best_guess',
            'key'            => config('services.google.maps_api_key'),
        ];
    }

    private function callDirectionsApi(array $params): array
    {
        $response = Http::timeout(config('services.google.directions_timeout', 10))
            ->get(config('services.google.directions_url'), $params);

        if ($response->failed()) {
            Log::error('RouteOptimizationService: HTTP error from Google Directions', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Google Directions API HTTP error: ' . $response->status());
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'OK') {
            Log::error('RouteOptimizationService: non-OK status from Google Directions', [
                'status'        => $data['status'] ?? 'unknown',
                'error_message' => $data['error_message'] ?? null,
            ]);
            throw new \RuntimeException('Google Directions API error: ' . ($data['status'] ?? 'unknown'));
        }

        return $data;
    }

    private function applyWaypointOrder(
        Collection $orders,
        array $waypointOrder,
        int $offset = 0
    ): Collection {
        $indexed = $orders->values();
        $result  = collect();

        foreach ($waypointOrder as $position => $googleIndex) {
            $order              = $indexed->get($googleIndex);
            $order->route_order = $offset + $position + 1;
            $result->push($order);
        }

        return $result;
    }

    private function persistRouteOrder(Collection $orders): void
    {
        foreach ($orders as $order) {
            Order::where('id', $order->id)->update(['route_order' => $order->route_order]);
        }
    }

    private function buildResult(
        Collection $orders,
        int $totalDistanceM,
        int $totalDurationS,
        ?string $polyline,
        int $chunks
    ): array {
        return [
            'driver_id'        => $orders->first()?->driver_id,
            'total_distance_m' => $totalDistanceM,
            'total_duration_s' => $totalDurationS,
            'polyline'         => $polyline,
            'chunks'           => $chunks,
            'optimized_at'     => now()->toISOString(),
            'orders'           => $orders
                ->sortBy('route_order')
                ->values()
                ->map(fn ($o) => [
                    'id'           => $o->id,
                    'order_number' => $o->order_number,
                    'route_order'  => $o->route_order,
                    'latitude'     => $o->receiver_latitude,
                    'longitude'    => $o->receiver_longitude,
                    'receiver'     => $o->receiver_name,
                    'address'      => $o->address_text,
                ])
                ->toArray(),
        ];
    }

    private function emptyResult(int $driverId): array
    {
        return [
            'driver_id'        => $driverId,
            'total_distance_m' => 0,
            'total_duration_s' => 0,
            'polyline'         => null,
            'chunks'           => 0,
            'optimized_at'     => now()->toISOString(),
            'orders'           => [],
        ];
    }
}
