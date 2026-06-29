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

        if ($user->isDriver()) {
            return $this->driverHome($user);
        }

        if ($user->isClientMaster() || $user->isClientEmployee()) {
            return $this->clientHome($user);
        }

        return response()->json([
            'success' => false,
            'message' => 'Home data not available for this account type.',
        ], 403);
    }

    private function driverHome($user): JsonResponse
    {
        $today = now()->toDateString();
        $driverProfileId = $user->driverProfile->id;

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->latest('check_in_at')
            ->first();

        $totalOrders = Order::where('driver_profile_id', $driverProfileId)
            ->where('status', 'picked_up')
            ->count();

        $assignedOrders = Order::where('driver_profile_id', $driverProfileId)
            ->where('status', 'assigned')
            ->count();

        $completedOrderIds = DB::table('order_tracking_logs')
            ->join('orders', 'order_tracking_logs.order_id', '=', 'orders.id')
            ->where('orders.driver_profile_id', $driverProfileId)
            ->where('order_tracking_logs.to_status', 'delivered')
            ->whereDate('order_tracking_logs.created_at', $today)
            ->distinct()
            ->pluck('order_tracking_logs.order_id');

        $rejectedOrderIds = DB::table('order_tracking_logs')
            ->join('orders', 'order_tracking_logs.order_id', '=', 'orders.id')
            ->where('orders.driver_profile_id', $driverProfileId)
            ->where('order_tracking_logs.to_status', 'rejected')
            ->whereDate('order_tracking_logs.created_at', $today)
            ->distinct()
            ->pluck('order_tracking_logs.order_id');

        $completedOrders = $completedOrderIds->count();
        $rejectedOrders = $rejectedOrderIds->count();

        $cashCollected = Order::where('driver_profile_id', $driverProfileId)
            ->where('payment_status', 'with_driver')
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->selectRaw(
                'COALESCE(SUM(order_payments.order_amount), 0)'
                . ' + COALESCE(SUM(CASE WHEN order_payments.delivery_on_customer = 1 THEN order_payments.customer_delivery_amount ELSE 0 END), 0)'
                . ' AS total'
            )
            ->value('total');

        $isCheckedIn = $attendance && $attendance->check_in_at && !$attendance->check_out_at;

        $ordersQuery = Order::with(['receiver.city', 'receiver.area', 'payment', 'rejectionReason'])
            ->where('driver_profile_id', $driverProfileId)
            ->where('status', 'picked_up')
            ->latest();

        $checkInAlert = null;
        if (!$isCheckedIn) {
            $hasHiddenOrders = Order::where('driver_profile_id', $driverProfileId)
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
            'alert' => $checkInAlert,
            'data' => [
                'attendance' => $attendance
                    ? new AttendanceResource($attendance)
                    : [
                        'id' => null,
                        'date' => $today,
                        'status' => 'not_checked_in',
                        'check_in_at' => null,
                        'check_out_at' => null,
                        'check_in_location' => null,
                        'check_out_location' => null,
                        'duration_minutes' => null,
                    ],
                'summary' => [
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'rejected_orders' => $rejectedOrders,
                    'assigned_orders' => $assignedOrders,
                    'cash_collected' => (float) ($cashCollected ?? 0),
                ],
                'orders' => OrderResource::collection($orders),
            ],
        ]);
    }

    private function clientHome($user): JsonResponse
    {
        $clientProfile = $user->isClientMaster()
            ? $user->clientProfile
            : $user->clientEmployee?->clientProfile;

        if (!$clientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Client profile not found.',
                'code' => 'CLIENT_PROFILE_NOT_FOUND',
            ], 403);
        }

        $summary = Order::where('client_profile_id', $clientProfile->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'picked_up' THEN 1 ELSE 0 END) as in_transit,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = 'returned'  THEN 1 ELSE 0 END) as returned,
                SUM(CASE WHEN status = 'rejected'  THEN 1 ELSE 0 END) as rejected
            ")
            ->first();

        $activeOrders = Order::with(['receiver.city', 'receiver.area', 'payment', 'driverProfile'])
            ->where('client_profile_id', $clientProfile->id)
            ->whereIn('status', ['pending', 'picked_up'])
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Home data retrieved successfully.',
            'data' => [
                'summary' => [
                    'total' => (int) $summary->total,
                    'pending' => (int) $summary->pending,
                    'in_transit' => (int) $summary->in_transit,
                    'delivered' => (int) $summary->delivered,
                    'returned' => (int) $summary->returned,
                    'rejected' => (int) $summary->rejected,
                ],
                'active_orders' => OrderResource::collection($activeOrders),
            ],
        ]);
    }
}
