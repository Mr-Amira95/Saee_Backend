<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AttendanceResource;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = $user->attendances();

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->input('month'))
                  ->whereYear('date', $request->input('year'));
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'not_checked_in' => $query->whereNull('check_in_at'),
                'checked_in'     => $query->whereNotNull('check_in_at')->whereNull('check_out_at'),
                'checked_out'    => $query->whereNotNull('check_out_at'),
                default          => null,
            };
        }

        $attendances = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Attendance records retrieved successfully',
            'data'    => AttendanceResource::collection($attendances->items()),
            'meta'    => [
                'current_page' => $attendances->currentPage(),
                'last_page'    => $attendances->lastPage(),
                'per_page'     => $attendances->perPage(),
                'total'        => $attendances->total(),
            ],
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $today = now()->toDateString();

        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today.',
                'code'    => 'ALREADY_CHECKED_IN',
            ], 422);
        }

        $attendance = Attendance::create([
            'user_id'           => $user->id,
            'date'              => $today,
            'check_in_at'       => now(),
            'check_in_location' => $request->input('location'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checked in successfully.',
            'data'    => new AttendanceResource($attendance),
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'You have not checked in today.',
                'code'    => 'NOT_CHECKED_IN',
            ], 422);
        }

        if ($attendance->check_out_at) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out today.',
                'code'    => 'ALREADY_CHECKED_OUT',
            ], 422);
        }

        $attendance->update([
            'check_out_at'       => now(),
            'check_out_location' => $request->input('location'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checked out successfully.',
            'data'    => new AttendanceResource($attendance),
        ]);
    }
}
