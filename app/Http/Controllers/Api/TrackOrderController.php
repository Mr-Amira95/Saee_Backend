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

        $orders = Order::with(['city', 'area', 'trackingLogs'])
            ->where(function ($query) use ($q) {
                $query->where('order_number', $q)
                      ->orWhere('receiver_name', 'like', "%{$q}%")
                      ->orWhere('receiver_phone', 'like', "%{$q}%");
            })
            ->latest()
            ->limit(10)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found matching your search.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orders found.',
            'data'    => $orders->map(fn ($order) => [
                'order_number'  => $order->order_number,
                'status'        => $order->status,
                'payment_type'  => $order->payment_type,
                'receiver_name' => $order->receiver_name,
                'address_text'  => $order->address_text,
                'city'          => $order->relationLoaded('city') && $order->city ? [
                    'name'    => $order->city->name,
                    'name_ar' => $order->city->name_ar,
                ] : null,
                'area'          => $order->relationLoaded('area') && $order->area ? [
                    'name'    => $order->area->name,
                    'name_ar' => $order->area->name_ar,
                ] : null,
                'tracking'      => $order->trackingLogs->map(fn ($log) => [
                    'from_status' => $log->from_status,
                    'to_status'   => $log->to_status,
                    'description' => $log->description,
                    'timestamp'   => $log->created_at->toDateTimeString(),
                ]),
                'created_at'    => $order->created_at?->toDateTimeString(),
            ]),
        ]);
    }
}
