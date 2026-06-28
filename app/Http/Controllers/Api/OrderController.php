<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderResource;
use App\Models\Area;
use App\Models\Attendance;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\OrderTrackingLog;
use App\Models\RejectionReason;
use App\Models\User;
use App\Services\OrderService;
use App\Services\SupportNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = Order::with(['payment', 'receiver.city', 'receiver.area', 'driverProfile.user', 'rejectionReason'])
            ->latest();

        $checkInAlert = null;

        if ($user->isDriver()) {
            $driverProfile = $user->driverProfile;
            $query->where('driver_profile_id', $driverProfile?->id);

            if (! $this->isDriverCheckedIn($user)) {
                $hasHiddenOrders = Order::where('driver_profile_id', $driverProfile?->id)
                    ->whereIn('status', ['picked_up', 'rejected'])
                    ->exists();

                if ($hasHiddenOrders) {
                    $checkInAlert = 'You have pending orders. Please check in to view your orders.';
                }

                $query->where('status', '!=', 'picked_up');
            }
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
            $query->whereHas('payment', fn ($pq) => $pq->where('payment_type', $request->input('payment_type')));
        }

        if ($request->filled('city_id')) {
            $query->whereHas('receiver', fn ($rq) => $rq->where('city_id', $request->input('city_id')));
        }

        if ($request->filled('area_id')) {
            $query->whereHas('receiver', fn ($rq) => $rq->where('area_id', $request->input('area_id')));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        if ($request->filled('batch_number')) {
            $query->where('batch_number', $request->input('batch_number'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('receiver', fn ($rq) => $rq
                      ->where('receiver_name', 'like', "%{$search}%")
                      ->orWhere('receiver_phone', 'like', "%{$search}%")
                  );
            });
        }

        $orders = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'alert'   => $checkInAlert,
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

        $order->load(['payment', 'receiver.city', 'receiver.area', 'driverProfile.user', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

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

        if (! $user->isDriver() || $order->driverProfile?->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (! $this->isDriverCheckedIn($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not checked in. Please check in to perform this action.',
                'code'    => 'NOT_CHECKED_IN',
            ], 403);
        }

        if ($order->status !== 'picked_up') {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be marked as delivered in its current status.',
                'code'    => 'INVALID_STATUS_TRANSITION',
            ], 422);
        }

        $requiresNationalId = (bool) $order->clientProfile?->require_national_id;

        $request->validate([
            'signature'              => ['required', 'file', 'image', 'max:5120'],
            'proof_image'            => ['nullable', 'file', 'image', 'max:5120'],
            'national_id_attachment' => [$requiresNationalId ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $signaturePath = $request->file('signature')
            ->store("orders/{$order->id}/signatures", 'public');

        $proofImagePath = $request->hasFile('proof_image')
            ? $request->file('proof_image')->store("orders/{$order->id}/proofs", 'public')
            : null;

        $nationalIdAttachmentPath = $request->hasFile('national_id_attachment')
            ? $request->file('national_id_attachment')->store("orders/{$order->id}/national-ids", 'public')
            : null;

        $order = $this->orderService->updateStatus($order, 'delivered', [
            'signature_path'              => $signaturePath,
            'proof_image_path'            => $proofImagePath,
            'national_id_attachment_path' => $nationalIdAttachmentPath,
        ], $user);

        $order->load(['payment', 'receiver.city', 'receiver.area', 'driverProfile.user', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

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

        if (! $user->isDriver() || $order->driverProfile?->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (! $this->isDriverCheckedIn($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not checked in. Please check in to perform this action.',
                'code'    => 'NOT_CHECKED_IN',
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

        $order->load(['payment', 'receiver.city', 'receiver.area', 'driverProfile.user', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

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

        if (! $user->isDriver() || $order->driverProfile?->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (! $this->isDriverCheckedIn($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not checked in. Please check in to perform this action.',
                'code'    => 'NOT_CHECKED_IN',
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

        $order->load(['payment', 'receiver.city', 'receiver.area', 'driverProfile.user', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as returned successfully.',
            'data'    => new OrderResource($order),
        ]);
    }

    public function confirmHandover(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $this->orderService->confirmHandover($user, $request->input('notes'));

        return response()->json([
            'success' => true,
            'message' => "Handover confirmed. {$result['returned']} order(s) returned, {$result['settled']} order(s) cash transferred to Saee.",
            'data'    => [
                'returned_count' => $result['returned'],
                'settled_count'  => $result['settled'],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster() && ! $user->isClientEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Only client accounts can create orders.',
            ], 403);
        }

        $clientProfile = $this->resolveClientProfile($user);

        if (! $clientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Client profile not found.',
                'code'    => 'CLIENT_PROFILE_NOT_FOUND',
            ], 403);
        }

        $request->validate([
            'order_description'        => ['nullable', 'string', 'max:255'],
            'payment_type'             => ['required', 'in:cod,prepaid'],
            'delivery_on_customer'     => ['nullable', 'boolean'],
            'delivery_customer_amount' => ['nullable', 'numeric', 'min:0'],
            'order_price'              => ['nullable', 'numeric', 'min:0'],
            'receiver_name'            => ['required', 'string', 'max:255'],
            'receiver_phone'           => ['required', 'string', 'max:20'],
            'city_id'                  => ['required', 'integer', 'exists:cities,id'],
            'area_id'                  => ['required', 'integer', 'exists:areas,id'],
            'address_text'             => ['required', 'string'],
            'notes'                    => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->input('payment_type') === 'cod' && ! $request->filled('order_price')) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => ['order_price' => ['Order price is required for COD orders.']],
            ], 422);
        }

        if ($request->boolean('delivery_on_customer') && ! $request->filled('delivery_customer_amount')) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => ['delivery_customer_amount' => ['Delivery customer amount is required when delivery is on customer.']],
            ], 422);
        }

        $order = $this->orderService->createOrder(array_merge($request->only([
            'order_description', 'payment_type', 'delivery_on_customer', 'delivery_customer_amount',
            'order_price', 'receiver_name', 'receiver_phone', 'city_id', 'area_id',
            'address_text', 'notes',
        ]), [
            'client_profile_id' => $clientProfile->id,
            'driver_id'         => null,
        ]), $user);

        $order->load(['payment', 'receiver.city', 'receiver.area', 'driverProfile.user', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsNewOrder($order));

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data'    => new OrderResource($order),
        ], 201);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster() && ! $user->isClientEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Only client accounts can edit orders.',
            ], 403);
        }

        $clientProfile = $this->resolveClientProfile($user);

        if (! $clientProfile || (int) $order->client_profile_id !== (int) $clientProfile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be edited.',
                'code'    => 'INVALID_STATUS_TRANSITION',
            ], 422);
        }

        $validated = $request->validate([
            'order_description'        => ['nullable', 'string', 'max:1000'],
            'payment_type'             => ['required', 'in:cod,prepaid'],
            'order_price'              => ['nullable', 'numeric', 'min:0'],
            'delivery_on_customer'     => ['nullable', 'boolean'],
            'delivery_customer_amount' => ['nullable', 'numeric', 'min:0'],
            'receiver_name'            => ['required', 'string', 'max:255'],
            'receiver_phone'           => ['required', 'string', 'max:20'],
            'city_id'                  => ['required', 'integer', 'exists:cities,id'],
            'area_id'                  => ['required', 'integer', 'exists:areas,id'],
            'address_text'             => ['required', 'string', 'max:1000'],
            'notes'                    => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['payment_type'] === 'cod' && ! $request->filled('order_price')) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => ['order_price' => ['Order price is required for COD orders.']],
            ], 422);
        }

        $deliveryOnCustomer = $request->boolean('delivery_on_customer');

        if ($deliveryOnCustomer && ! $request->filled('delivery_customer_amount')) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => ['delivery_customer_amount' => ['Delivery customer amount is required when delivery is on customer.']],
            ], 422);
        }

        $order->load(['payment', 'receiver']);

        $cityChanged = $order->receiver->city_id !== (int) $validated['city_id'];
        $clientDeliveryAmount = $cityChanged
            ? $clientProfile->getDeliveryPriceForCity((int) $validated['city_id'])
            : $order->payment->client_delivery_amount;

        $order->update([
            'order_description' => $validated['order_description'] ?? null,
            'notes'             => $validated['notes'] ?? null,
        ]);

        $order->payment->update([
            'payment_type'             => $validated['payment_type'],
            'order_amount'             => $validated['payment_type'] === 'cod' ? (float) ($validated['order_price'] ?? 0) : null,
            'delivery_on_customer'     => $deliveryOnCustomer,
            'customer_delivery_amount' => $deliveryOnCustomer ? (float) ($validated['delivery_customer_amount'] ?? 0) : null,
            'client_delivery_amount'   => $clientDeliveryAmount,
        ]);

        $order->receiver->update([
            'receiver_name'  => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'city_id'        => (int) $validated['city_id'],
            'area_id'        => (int) $validated['area_id'],
            'address_text'   => $validated['address_text'],
        ]);

        $order->load(['payment', 'receiver.city', 'receiver.area', 'driverProfile.user', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'data'    => new OrderResource($order),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster() && ! $user->isClientEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $clientProfile = $this->resolveClientProfile($user);

        if (! $clientProfile || (int) $order->client_profile_id !== (int) $clientProfile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled.',
                'code'    => 'INVALID_STATUS_TRANSITION',
            ], 422);
        }

        $order = $this->orderService->updateStatus($order, 'cancelled', [], $user);

        $order->load(['payment', 'receiver.city', 'receiver.area', 'driverProfile.user', 'clientProfile', 'rejectionReason', 'trackingLogs.user']);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'data'    => new OrderResource($order),
        ]);
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster() && ! $user->isClientEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $clientProfile = $this->resolveClientProfile($user);

        if (! $clientProfile || (int) $order->client_profile_id !== (int) $clientProfile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled.',
                'code'    => 'INVALID_STATUS_TRANSITION',
            ], 422);
        }

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsCancelOrder($order));

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
        ]);
    }

    public function importOrders(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster() && ! $user->isClientEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Only client accounts can import orders.',
            ], 403);
        }

        $clientProfile = $this->resolveClientProfile($user);

        if (! $clientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Client profile not found.',
                'code'    => 'CLIENT_PROFILE_NOT_FOUND',
            ], 403);
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');
            $expected = ['order_description', 'payment_type', 'delivery_on_customer', 'delivery_customer_amount', 'order_price', 'receiver_name', 'receiver_phone', 'city_id', 'area_id', 'address_text', 'notes'];

            if (! $headers || count(array_intersect($headers, $expected)) < 5) {
                fclose($handle);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid CSV format. Please download and use the provided template.',
                ], 422);
            }

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($headers) === count($row)) {
                    $rows[] = array_combine($headers, $row);
                }
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'The CSV file is empty.',
            ], 422);
        }

        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum    = $index + 2;
            $rowErrors = [];

            $paymentType = strtolower($row['payment_type'] ?? '');
            if (! in_array($paymentType, ['cod', 'prepaid'])) {
                $rowErrors[] = "Payment type must be 'cod' or 'prepaid'.";
            }

            $orderPrice = filter_var($row['order_price'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($paymentType === 'cod' && ($orderPrice === false || $orderPrice < 0)) {
                $rowErrors[] = 'Order price must be a positive number for COD orders.';
            }

            $deliveryOnCustomer = filter_var($row['delivery_on_customer'] ?? 'false', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($deliveryOnCustomer === null) {
                $rowErrors[] = "delivery_on_customer must be 'true' or 'false'.";
            }

            $deliveryCustomerAmt = filter_var($row['delivery_customer_amount'] ?? 0, FILTER_VALIDATE_FLOAT);
            if ($deliveryOnCustomer && ($deliveryCustomerAmt === false || $deliveryCustomerAmt < 0)) {
                $rowErrors[] = 'delivery_customer_amount must be a valid number.';
            }

            $cityId = filter_var($row['city_id'] ?? null, FILTER_VALIDATE_INT);
            if (! $cityId || ! City::where('id', $cityId)->exists()) {
                $rowErrors[] = "City ID [{$row['city_id']}] does not exist.";
            }

            $areaId = filter_var($row['area_id'] ?? null, FILTER_VALIDATE_INT);
            if (! $areaId || ! Area::where('id', $areaId)->where('city_id', $cityId)->exists()) {
                $rowErrors[] = "Area ID [{$row['area_id']}] does not exist or does not belong to City [{$row['city_id']}].";
            }

            if (empty($row['receiver_name']))  { $rowErrors[] = 'Receiver name is required.'; }
            if (empty($row['receiver_phone'])) { $rowErrors[] = 'Receiver phone is required.'; }
            if (empty($row['address_text']))   { $rowErrors[] = 'Address is required.'; }

            if (! empty($rowErrors)) {
                $errors[] = ['row' => $rowNum, 'errors' => $rowErrors];
            }
        }

        if (! empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'CSV validation failed. Fix the errors and re-upload.',
                'errors'  => $errors,
            ], 422);
        }

        $batchNumber   = 'BATCH-' . now()->format('ymd') . '-' . $clientProfile->id . '-' . strtoupper(substr(md5(uniqid()), 0, 4));
        $importedCount = 0;

        foreach ($rows as $row) {
            $this->orderService->createOrder([
                'client_profile_id'        => $clientProfile->id,
                'driver_id'                => null,
                'order_description'        => $row['order_description'] ?? null,
                'payment_type'             => strtolower($row['payment_type']),
                'delivery_on_customer'     => filter_var($row['delivery_on_customer'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
                'delivery_customer_amount' => isset($row['delivery_customer_amount']) ? (float) $row['delivery_customer_amount'] : 0.0,
                'order_price'              => isset($row['order_price']) ? (float) $row['order_price'] : 0.0,
                'receiver_name'            => $row['receiver_name'],
                'receiver_phone'           => $row['receiver_phone'],
                'city_id'                  => (int) $row['city_id'],
                'area_id'                  => (int) $row['area_id'],
                'address_text'             => $row['address_text'],
                'notes'                    => $row['notes'] ?? null,
                'batch_number'             => $batchNumber,
            ], $user);

            $importedCount++;
        }

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsOrdersImported(
            $clientProfile->company_name,
            $importedCount,
            $batchNumber,
        ));

        return response()->json([
            'success'      => true,
            'message'      => "{$importedCount} order(s) imported successfully.",
            'batch_number' => $batchNumber,
            'imported'     => $importedCount,
        ], 201);
    }

    public function downloadImportTemplate(): StreamedResponse
    {
        $headers = ['order_description', 'payment_type', 'delivery_on_customer', 'delivery_customer_amount', 'order_price', 'receiver_name', 'receiver_phone', 'city_id', 'area_id', 'address_text', 'notes'];
        $sample  = ['E-commerce parcel', 'cod', 'false', '0.00', '150.00', 'Ahmed Mansour', '0501234567', '1', '2', 'King Fahd Road, Al Malaz', 'Deliver after 5 PM'];

        $callback = function () use ($headers, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders_import_template.csv"',
            'Cache-Control'       => 'no-cache',
        ]);
    }

    private function isDriverCheckedIn(User $user): bool
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->latest('check_in_at')
            ->first();

        return $attendance && $attendance->check_in_at && ! $attendance->check_out_at;
    }

    private function canAccessOrder(User $user, Order $order): bool
    {
        if ($user->isDriver()) {
            $driverProfile = $user->driverProfile;
            return $driverProfile && (int) $order->driver_profile_id === (int) $driverProfile->id;
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

    private function resolveClientProfile(User $user): ?ClientProfile
    {
        if ($user->isClientMaster()) {
            return $user->clientProfile;
        }

        if ($user->isClientEmployee()) {
            return $user->clientEmployee?->clientProfile;
        }

        return null;
    }
}
