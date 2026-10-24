<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AttendanceResource;
use App\Http\Resources\Api\OrderResource;
use App\Models\Attendance;
use App\Models\Order;
use App\Services\ClientDashboardService;
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
            'message' => __('Home data not available for this account type.'),
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

        $cashOrders = Order::with(['receiver', 'payment'])
            ->where('driver_profile_id', $driverProfileId)
            ->where('payment_status', 'with_driver')
            ->whereHas('payment')
            ->latest('delivered_at')
            ->get()
            ->map(function (Order $order) {
                $orderAmount = (float) ($order->payment->order_amount ?? 0);
                $deliveryAmount = $order->payment->delivery_on_customer
                    ? (float) ($order->payment->customer_delivery_amount ?? 0)
                    : 0.0;

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'receiver_name' => $order->receiver?->receiver_name,
                    'order_amount' => $orderAmount,
                    'delivery_amount' => $deliveryAmount,
                    'collected_amount' => $orderAmount + $deliveryAmount,
                    'delivered_at' => $order->delivered_at?->toDateTimeString(),
                ];
            });

        $isCheckedIn = $attendance && $attendance->check_in_at && !$attendance->check_out_at;

        $ordersQuery = Order::with(['receiver.city', 'receiver.area', 'payment', 'rejectionReason'])
            ->where('driver_profile_id', $driverProfileId)
            ->whereIn('status', ['picked_up', 'assigned'])
            ->latest();

        $checkInAlert = null;

        $orders = $ordersQuery->get();

        return response()->json([
            'success' => true,
            'message' => __('Home data retrieved successfully.'),
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
                    'cash_collected' => [
                        'total' => (float) $cashOrders->sum('collected_amount'),
                        'orders' => $cashOrders->values(),
                    ],
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
                'message' => __('Client profile not found.'),
                'code' => 'CLIENT_PROFILE_NOT_FOUND',
            ], 403);
        }

        $summary = Order::where('client_profile_id', $clientProfile->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status IN ('assigned', 'picked_up') THEN 1 ELSE 0 END) as in_transit,
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

        $metrics = app(ClientDashboardService::class)->metrics($clientProfile->id);

        $last7Days = [];
        foreach ($metrics['days_trend'] as $date => $count) {
            $last7Days[] = ['date' => $date, 'count' => $count];
        }

        return response()->json([
            'success' => true,
            'message' => __('Home data retrieved successfully.'),
            'data' => [
                'summary' => [
                    'total' => (int) $summary->total,
                    'pending' => (int) $summary->pending,
                    'in_transit' => (int) $summary->in_transit,
                    'delivered' => (int) $summary->delivered,
                    'returned' => (int) $summary->returned,
                    'rejected' => (int) $summary->rejected,
                ],
                'dashboard' => [
                    'pending_cash' => $metrics['pending_cash'],
                    'account_balance' => $metrics['balance'],
                    'shipping_volume' => [
                        'total' => array_sum($metrics['days_trend']),
                        'last_7_days' => $last7Days,
                    ],
                    'pending_pickup' => $metrics['stats']['pending'],
                    'in_transit' => $metrics['stats']['picked_up'],
                    'delivered_today' => $metrics['stats']['delivered_today'],
                    'returned_failed' => $metrics['stats']['returned'],
                ],
                'active_orders' => OrderResource::collection($activeOrders),
            ],
        ]);
    }
}
