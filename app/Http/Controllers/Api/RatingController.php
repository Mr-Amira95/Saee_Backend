<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RatingFilterRequest;
use App\Http\Resources\Api\DriverRatingResource;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    public function index(RatingFilterRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = $user->driverRatings()->with('order');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('stars')) {
            $query->where('rating', $request->integer('stars'));
        }

        $averageRating = round((clone $query)->avg('rating') ?? 0, 1);

        $ratings = $query->latest()->paginate(20);

        return response()->json([
            'success'        => true,
            'average_rating' => $averageRating,
            'data'           => DriverRatingResource::collection($ratings->items()),
            'meta'           => [
                'current_page' => $ratings->currentPage(),
                'last_page'    => $ratings->lastPage(),
                'per_page'     => $ratings->perPage(),
                'total'        => $ratings->total(),
            ],
        ]);
    }
}
