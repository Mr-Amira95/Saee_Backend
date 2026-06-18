<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RejectionReasonResource;
use App\Models\RejectionReason;
use Illuminate\Http\JsonResponse;

class RejectionReasonController extends Controller
{
    public function index(): JsonResponse
    {
        $reasons = RejectionReason::where('is_active', true)
            ->orderBy('reason')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Rejection reasons retrieved successfully.',
            'data'    => RejectionReasonResource::collection($reasons),
        ]);
    }
}
