<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderTrackingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicOrderLocationController extends Controller
{
    public function show($order_number)
    {
        $order = Order::where('order_number', $order_number)
            ->with(['clientProfile', 'driverProfile.user', 'driverRating'])
            ->firstOrFail();

        return view('public.share_location', compact('order'));
    }

    public function update(Request $request, $order_number)
    {
        $order = Order::where('order_number', $order_number)->firstOrFail();

        if ($request->has('rating')) {
            $request->validate([
                'rating'  => 'required|integer|between:1,5',
                'comment' => 'nullable|string|max:1000',
            ]);

            if (! $order->driver_profile_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No driver is assigned to this order to rate.',
                ], 422);
            }

            $driverUserId = $order->driverProfile?->user_id;

            \App\Models\DriverRating::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'driver_id' => $driverUserId,
                    'rating'    => $request->input('rating'),
                    'comment'   => $request->input('comment'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!',
            ]);
        }

        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        DB::transaction(function () use ($order, $request) {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');

            // Store coordinates in the receiver record
            $order->receiver()->update([
                'receiver_latitude'    => $lat,
                'receiver_longitude'   => $lng,
                'location_received_at' => now(),
            ]);

            OrderTrackingLog::create([
                'order_id'    => $order->id,
                'user_id'     => $order->driverProfile?->user_id ?? $order->clientProfile->master_user_id,
                'from_status' => $order->status,
                'to_status'   => $order->status,
                'description' => "Customer shared coordinates via WhatsApp link: {$lat},{$lng}.",
                'latitude'    => $lat,
                'longitude'   => $lng,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully! Thank you for sharing.',
        ]);
    }
}
