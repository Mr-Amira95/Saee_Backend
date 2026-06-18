<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CityResource;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    public function index(): JsonResponse
    {
        $cities = City::with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Cities retrieved successfully.',
            'data'    => CityResource::collection($cities),
        ]);
    }
}
