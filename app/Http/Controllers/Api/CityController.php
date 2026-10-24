<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CityResource;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cities = City::with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Clients get their custom prices; anyone else (or cities without one) gets the default.
        $customPrices = $request->user()
            ?->resolveClientProfile()
            ?->deliveryPrices()
            ->pluck('delivery_price', 'city_id')
            ?? collect();

        $cities->each(fn (City $city) => $city->setAttribute(
            'client_delivery_price',
            (float) ($customPrices[$city->id] ?? $city->delivery_price)
        ));

        return response()->json([
            'success' => true,
            'message' => __('Cities retrieved successfully.'),
            'data'    => CityResource::collection($cities),
        ]);
    }
}
