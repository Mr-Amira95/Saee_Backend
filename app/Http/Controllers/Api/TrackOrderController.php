<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:3', 'max:100'],
        ]);

        $q = trim($request->input('q'));

        $orders = Order::with(['receiver.city', 'receiver.area', 'trackingLogs'])
            ->where(function ($query) use ($q) {
                $query->where('order_number', $q)
                      ->orWhereHas('receiver', fn ($rq) => $rq
                          ->where('receiver_name', 'like', "%{$q}%")
                          ->orWhere('receiver_phone', 'like', "%{$q}%")
                      );
            })
            ->latest()
            ->limit(10)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('No orders found matching your search.'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('Orders found.'),
            'data'    => $orders->map(fn ($order) => [
                'order_id'      => $order->id,
                'order_number'  => $order->order_number,
                'status'        => $order->status,
                'payment_type'  => $order->payment?->payment_type,
                'receiver_name' => $order->receiver?->receiver_name,
                'address_text'  => $order->receiver?->address_text,
                'city'          => $order->receiver?->city ? [
                    'name'    => $order->receiver->city->name,
                    'name_ar' => $order->receiver->city->name_ar,
                ] : null,
                'area'          => $order->receiver?->area ? [
                    'name'    => $order->receiver->area->name,
                    'name_ar' => $order->receiver->area->name_ar,
                ] : null,
                'tracking'      => $this->visibleTrackingLogs($order)->map(fn ($log) => [
                    'from_status' => $log->from_status,
                    'to_status'   => $log->to_status,
                    'description' => $log->description,
                    'timestamp'   => $log->created_at->toDateTimeString(),
                ]),
                'created_at'    => $order->created_at?->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Hide any tracking events logged after the order reached a terminal
     * status (delivered/rejected), so the customer never sees internal
     * post-delivery/return handling.
     */
    private function visibleTrackingLogs(Order $order)
    {
        $chronological = $order->trackingLogs->sortBy('created_at')->values();

        $terminalIndex = $chronological->search(
            fn ($log) => in_array($log->to_status, ['delivered', 'rejected'], true)
        );

        if ($terminalIndex !== false) {
            $chronological = $chronological->slice(0, $terminalIndex + 1);
        }

        return $chronological->sortByDesc('created_at')->values();
    }
}
