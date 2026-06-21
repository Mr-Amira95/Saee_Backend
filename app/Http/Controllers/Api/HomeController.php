<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AttendanceResource;
use App\Http\Resources\Api\OrderResource;
use App\Models\Attendance;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only drivers can access this resource.',
            ], 403);
        }

        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->latest('check_in_at')
            ->first();

        // Orders "today" = orders assigned to this driver today, identified via the tracking log.
        // Using created_at would miss orders created on a prior day and assigned today.
        $todayOrderIds = DB::table('order_tracking_logs')
            ->join('orders', 'order_tracking_logs.order_id', '=', 'orders.id')
            ->where('orders.driver_id', $user->id)
            ->where('order_tracking_logs.to_status', 'picked_up')
            ->whereDate('order_tracking_logs.created_at', $today)
            ->distinct()
            ->pluck('order_tracking_logs.order_id');

        $totalOrders = Order::whereIn('id', $todayOrderIds)->count();

        $completedOrders = Order::whereIn('id', $todayOrderIds)
            ->where('status', 'delivered')
            ->count();

        $rejectedOrders = Order::whereIn('id', $todayOrderIds)
            ->where('status', 'rejected')
            ->count();

        $cashCollected = Order::whereIn('id', $todayOrderIds)
            ->where('payment_type', 'cod')
            ->where('status', 'delivered')
            ->selectRaw(
                'COALESCE(SUM(order_price), 0)'
                . ' + COALESCE(SUM(CASE WHEN delivery_on_customer = 1 THEN delivery_customer_amount ELSE 0 END), 0)'
                . ' AS total'
            )
            ->value('total');

        $isCheckedIn = $attendance && $attendance->check_in_at && ! $attendance->check_out_at;

        $ordersQuery = Order::with(['city', 'area', 'rejectionReason'])
            ->where('driver_id', $user->id)
            ->where(function ($q) use ($todayOrderIds, $isCheckedIn) {
                $q->whereIn('id', $todayOrderIds);
                if ($isCheckedIn) {
                    $q->orWhere('status', 'picked_up');
                }
            })
            ->latest();

        $checkInAlert = null;
        if (! $isCheckedIn) {
            $hasHiddenOrders = Order::where('driver_id', $user->id)
                ->whereIn('status', ['picked_up', 'rejected'])
                ->exists();

            if ($hasHiddenOrders) {
                $checkInAlert = 'You have pending orders. Please check in to view your orders.';
            }
        }

        $orders = $ordersQuery->get();

        return response()->json([
            'success' => true,
            'message' => 'Home data retrieved successfully.',
            'alert'   => $checkInAlert,
            'data'    => [
                'attendance' => $attendance
                    ? new AttendanceResource($attendance)
                    : [
                        'id'                 => null,
                        'date'               => $today,
                        'status'             => 'not_checked_in',
                        'check_in_at'        => null,
                        'check_out_at'       => null,
                        'check_in_location'  => null,
                        'check_out_location' => null,
                        'duration_minutes'   => null,
                    ],
                'summary' => [
                    'total_orders'     => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'rejected_orders'  => $rejectedOrders,
                    'cash_collected'   => (float) ($cashCollected ?? 0),
                ],
                'orders' => OrderResource::collection($orders),
            ],
        ]);
    }
}
