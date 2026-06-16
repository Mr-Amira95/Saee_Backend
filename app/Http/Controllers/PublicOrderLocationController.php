<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderTrackingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicOrderLocationController extends Controller
{
    /**
     * Show the public location sharing page.
     */
    public function show($order_number)
    {
        $order = Order::where('order_number', $order_number)
            ->with(['clientProfile', 'driver', 'driverRating'])
            ->firstOrFail();

        return view('public.share_location', compact('order'));
    }

    /**
     * Update the order with customer location coordinates or driver rating.
     */
    public function update(Request $request, $order_number)
    {
        $order = Order::where('order_number', $order_number)->firstOrFail();

        // Handle rating submission
        if ($request->has('rating')) {
            $request->validate([
                'rating'  => 'required|integer|between:1,5',
                'comment' => 'nullable|string|max:1000',
            ]);

            if (!$order->driver_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No driver is assigned to this order to rate.'
                ], 422);
            }

            \App\Models\DriverRating::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'driver_id' => $order->driver_id,
                    'rating'    => $request->input('rating'),
                    'comment'   => $request->input('comment'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!'
            ]);
        }

        // Handle location coordinates sharing
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        DB::transaction(function () use ($order, $request) {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');

            // Update order address location
            $order->address_location = "{$lat},{$lng}";
            $order->save();

            // Create tracking log entry
            OrderTrackingLog::create([
                'order_id'    => $order->id,
                'user_id'     => $order->driver_id ?? $order->clientProfile->master_user_id, // Assign to driver or client master if no driver
                'from_status' => $order->status,
                'to_status'   => $order->status,
                'description' => "Customer shared coordinates via WhatsApp link: {$lat},{$lng}.",
                'latitude'    => $lat,
                'longitude'   => $lng,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully! Thank you for sharing.'
        ]);
    }
}
