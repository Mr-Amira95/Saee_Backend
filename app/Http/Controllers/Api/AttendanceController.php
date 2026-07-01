<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AttendanceResource;
use App\Models\Attendance;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        $openSession = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNull('check_out_at')
            ->first();

        if ($openSession) {
            return response()->json([
                'success' => false,
                'message' => 'You are already checked in. Please check out first.',
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
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'You are not currently checked in.',
                'code'    => 'NOT_CHECKED_IN',
            ], 422);
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Shift summary generated. Confirm to check out.',
            'data'          => new AttendanceResource($attendance),
            'shift_summary' => $this->buildShiftSummary($user->driverProfile->id, $attendance->check_in_at),
        ]);
    }

    public function confirmCheckOut(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'You are not currently checked in.',
                'code'    => 'NOT_CHECKED_IN',
            ], 422);
        }

        $shiftSummary = $this->buildShiftSummary($user->driverProfile->id, $attendance->check_in_at);

        $attendance->update([
            'check_out_at'       => now(),
            'check_out_location' => $request->input('location'),
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Checked out successfully.',
            'data'          => new AttendanceResource($attendance),
            'shift_summary' => $shiftSummary,
        ]);
    }

    private function buildShiftSummary(int $driverId, Carbon $checkInAt): array
    {
        $deliveredOrders = Order::with(['clientProfile', 'rejectionReason', 'payment', 'receiver'])
            ->where('driver_profile_id', $driverId)
            ->where('status', 'delivered')
            ->where('updated_at', '>=', $checkInAt)
            ->get();

        $returnedOrders = Order::with(['clientProfile', 'payment', 'receiver'])
            ->where('driver_profile_id', $driverId)
            ->where('status', 'returned')
            ->where('updated_at', '>=', $checkInAt)
            ->get();

        $rejectedOrders = Order::with(['clientProfile', 'rejectionReason', 'receiver'])
            ->where('driver_profile_id', $driverId)
            ->where('status', 'rejected')
            ->where('updated_at', '>=', $checkInAt)
            ->get();

        // Cash physically with the driver across all time (not yet settled)
        $cashToHandover = (float) Order::where('driver_profile_id', $driverId)
            ->where('payment_status', 'with_driver')
            ->whereNull('handover_request_id')
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->sum(\DB::raw('COALESCE(order_payments.order_amount, 0) + COALESCE(order_payments.customer_delivery_amount, 0)'));

        // COD cash collected during this shift only
        $shiftCodCollected = (float) $deliveredOrders
            ->filter(fn ($o) => $o->payment?->payment_type === 'cod')
            ->sum(fn ($o) => (float) ($o->payment?->order_amount ?? 0) + ($o->payment?->delivery_on_customer ? (float) ($o->payment?->customer_delivery_amount ?? 0) : 0));

        $mapOrder = fn (Order $o) => [
            'order_number'    => $o->order_number,
            'receiver_name'   => $o->receiver?->receiver_name,
            'receiver_phone'  => $o->receiver?->receiver_phone,
            'city'            => $o->city?->name,
            'client'          => $o->clientProfile?->company_name,
            'payment_type'    => $o->payment?->payment_type,
            'order_price'     => (float) ($o->payment?->order_amount ?? 0),
            'delivery_amount' => (float) ($o->payment?->customer_delivery_amount ?? 0),
            'payment_status'  => $o->payment_status,
        ];

        $mapRejected = fn (Order $o) => [
            'order_number'     => $o->order_number,
            'receiver_name'    => $o->receiver?->receiver_name,
            'receiver_phone'   => $o->receiver?->receiver_phone,
            'city'             => $o->city?->name,
            'client'           => $o->clientProfile?->company_name,
            'rejection_reason' => $o->rejectionReason?->reason,
        ];

        $mapReturned = fn (Order $o) => [
            'order_number'   => $o->order_number,
            'receiver_name'  => $o->receiver?->receiver_name,
            'receiver_phone' => $o->receiver?->receiver_phone,
            'city'           => $o->city?->name,
            'client'         => $o->clientProfile?->company_name,
            'payment_type'   => $o->payment?->payment_type,
            'order_price'    => (float) ($o->payment?->order_amount ?? 0),
        ];

        return [
            'total_orders'    => $deliveredOrders->count() + $returnedOrders->count() + $rejectedOrders->count(),
            'delivered_count' => $deliveredOrders->count(),
            'returned_count'  => $returnedOrders->count(),
            'rejected_count'  => $rejectedOrders->count(),
            'money_summary'   => [
                'cash_to_handover'   => $cashToHandover,
                'shift_cod_collected' => $shiftCodCollected,
            ],
            'delivered_orders' => $deliveredOrders->map($mapOrder)->values(),
            'returned_orders'  => $returnedOrders->map($mapReturned)->values(),
            'rejected_orders'  => $rejectedOrders->map($mapRejected)->values(),
        ];
    }
}
