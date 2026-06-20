<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Models\OrderTrackingLog;
use App\Models\RejectionReason;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = Order::with(['city', 'area', 'driver', 'rejectionReason'])
            ->latest();

        if ($user->isDriver()) {
            $query->where('driver_id', $user->id);
        } elseif ($user->isClientMaster()) {
            $clientProfile = $user->clientProfile;
            if (! $clientProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client profile not found.',
                    'code'    => 'CLIENT_PROFILE_NOT_FOUND',
                ], 403);
            }
            $query->where('client_profile_id', $clientProfile->id);
        } elseif ($user->isClientEmployee()) {
            $employee = $user->clientEmployee;
            if (! $employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee profile not found.',
                    'code'    => 'EMPLOYEE_PROFILE_NOT_FOUND',
                ], 403);
            }
            $query->where('client_profile_id', $employee->client_profile_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->input('payment_type'));
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->input('city_id'));
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('receiver_name', 'like', "%{$search}%")
                  ->orWhere('receiver_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data'    => OrderResource::collection($orders->items()),
            'meta'    => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

if (! $this->canAccessOrder($user, $order)) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        $order->load(['city', 'area', 'driver', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully.',
            'data'    => new OrderResource($order),
        ]);
    }

    public function deliver(Request $request, Order $order): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isDriver() || (int) $order->driver_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($order->status !== 'picked_up') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be marked as delivered in its current status.',
                'code'    => 'INVALID_STATUS_TRANSITION',
            ], 422);
        }

        $request->validate([
            'signature'   => ['required', 'file', 'image', 'max:5120'],
            'proof_image' => ['nullable', 'file', 'image', 'max:5120'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $signaturePath = $request->file('signature')
            ->store("orders/{$order->id}/signatures", 'public');

        $proofImagePath = $request->hasFile('proof_image')
            ? $request->file('proof_image')->store("orders/{$order->id}/proofs", 'public')
            : null;

        $order->update([
            'status'           => 'delivered',
            'payment_status'   => $order->payment_type === 'cod' ? 'with_driver' : $order->payment_status,
            'signature_path'   => $signaturePath,
            'proof_image_path' => $proofImagePath,
        ]);

        OrderTrackingLog::create([
            'order_id'    => $order->id,
            'user_id'     => $user->id,
            'from_status' => 'picked_up',
            'to_status'   => 'delivered',
            'description' => 'Order delivered successfully.',
            'latitude'    => $request->input('latitude'),
            'longitude'   => $request->input('longitude'),
        ]);

        $order->load(['city', 'area', 'driver', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as delivered successfully.',
            'data'    => new OrderResource($order),
        ]);
    }

    public function reject(Request $request, Order $order): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isDriver() || (int) $order->driver_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($order->status !== 'picked_up') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be rejected in its current status.',
                'code'    => 'INVALID_STATUS_TRANSITION',
            ], 422);
        }

        $request->validate([
            'rejection_reason_id' => [
                'required',
                'integer',
                Rule::exists('rejection_reasons', 'id')->where('is_active', true),
            ],
            'notes'     => ['nullable', 'string', 'max:1000'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $reason = RejectionReason::find($request->input('rejection_reason_id'));

        $order->update([
            'status'              => 'rejected',
            'rejection_reason_id' => $reason->id,
            'notes'               => $request->input('notes'),
        ]);

        OrderTrackingLog::create([
            'order_id'    => $order->id,
            'user_id'     => $user->id,
            'from_status' => 'picked_up',
            'to_status'   => 'rejected',
            'description' => "Order rejected: {$reason->reason}",
            'latitude'    => $request->input('latitude'),
            'longitude'   => $request->input('longitude'),
        ]);

        $order->load(['city', 'area', 'driver', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

        return response()->json([
            'success' => true,
            'message' => 'Order rejected successfully.',
            'data'    => new OrderResource($order),
        ]);
    }

    public function returnOrder(Request $request, Order $order): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isDriver() || $order->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($order->status !== 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Only rejected orders can be marked as returned.',
                'code'    => 'INVALID_STATUS_TRANSITION',
            ], 422);
        }

        $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $order->update([
            'status'         => 'returned',
            'payment_status' => 'no_payment',
        ]);

        OrderTrackingLog::create([
            'order_id'    => $order->id,
            'user_id'     => $user->id,
            'from_status' => 'rejected',
            'to_status'   => 'returned',
            'description' => 'Order returned to hub/client by driver.',
            'latitude'    => $request->input('latitude'),
            'longitude'   => $request->input('longitude'),
        ]);

        $order->load(['city', 'area', 'driver', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as returned successfully.',
            'data'    => new OrderResource($order),
        ]);
    }

    private function canAccessOrder(User $user, Order $order): bool
    {
        if ($user->isDriver()) {
            return (int) $order->driver_id === (int) $user->id;
        }

        if ($user->isClientMaster()) {
            $clientProfile = $user->clientProfile;
            return $clientProfile && (int) $order->client_profile_id === (int) $clientProfile->id;
        }

        if ($user->isClientEmployee()) {
            $employee = $user->clientEmployee;
            return $employee && (int) $order->client_profile_id === (int) $employee->client_profile_id;
        }

        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
