<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Models\OrderTrackingLog;
use App\Models\ClientProfile;
use App\Models\City;
use App\Models\User;
use App\Models\RejectionReason;
use App\Services\OrderService;
use App\Services\SupportNotificationService;
use App\Services\WaybillExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Final statuses in which the driver assignment is locked and the
     * order is no longer eligible for reassignment.
     */
    protected const DRIVER_LOCKED_STATUSES = ['delivered', 'rejected', 'cancelled', 'returned'];

    /**
     * Grouped values accepted by the `status` filter, each covering several
     * order statuses (used by the merged stats card).
     */
    protected const STATUS_GROUPS = [
        'rejected_returned_cancelled' => ['rejected', 'returned', 'cancelled'],
    ];

    protected const ORDER_STATUSES = ['pending', 'assigned', 'picked_up', 'delivered', 'rejected', 'returned', 'cancelled'];

    protected const PAYMENT_STATUSES = ['pending', 'with_driver', 'with_company', 'paid', 'no_payment'];

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = $this->getFilteredQuery($request, true);
        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $clients = ClientProfile::orderBy('company_name')->get();
        $drivers = User::where('role', 'driver')->where('status', 'active')->orderBy('name')->get();
        $cities = City::where('is_active', true)->orderBy('name')->get();

        // Stats ignore the status filter so every card keeps its count while one is selected
        $statusCounts = $this->getFilteredQuery($request, false, false)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'pending'                     => $statusCounts['pending'] ?? 0,
            'assigned'                    => $statusCounts['assigned'] ?? 0,
            'picked_up'                   => $statusCounts['picked_up'] ?? 0,
            'delivered'                   => $statusCounts['delivered'] ?? 0,
            'rejected_returned_cancelled' => collect(self::STATUS_GROUPS['rejected_returned_cancelled'])
                ->sum(fn ($status) => $statusCounts[$status] ?? 0),
        ];

        return view('admin.orders.index', compact('orders', 'clients', 'drivers', 'cities', 'stats'));
    }

    public function export(Request $request)
    {
        $orders = $this->getFilteredQuery($request, true)->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders_export_' . now()->format('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($file, [
                'Order Number',
                'Batch Number',
                'Status',
                'Payment Status',
                'Client Name',
                'Driver Name',
                'Receiver Name',
                'Receiver Phone',
                'City',
                'Area',
                'Address',
                'Payment Type',
                'Order Price (COD)',
                'Delivery Customer Amount',
                'Delivery Shift',
                'Created At'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->batch_number,
                    $order->status,
                    $order->payment_status,
                    $order->clientProfile?->company_name ?? 'N/A',
                    $order->driverProfile?->user?->name ?? 'N/A',
                    $order->receiver?->receiver_name,
                    $order->receiver?->receiver_phone,
                    $order->receiver?->city?->name ?? 'N/A',
                    $order->receiver?->area?->name ?? 'N/A',
                    $order->receiver?->address_text,
                    $order->payment?->payment_type,
                    $order->payment?->order_amount,
                    $order->payment?->customer_delivery_amount,
                    $order->delivery_shift?->label(),
                    $order->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printAll(Request $request, WaybillExportService $waybills)
    {
        return $waybills->render($this->getFilteredQuery($request, false)->orderBy('created_at', 'desc'));
    }

    public function printOrder(Order $order, WaybillExportService $waybills)
    {
        return $waybills->render($order);
    }

    public function create()
    {
        $clients = ClientProfile::where('status', 'active')->with('deliveryPrices')->orderBy('company_name')->get();
        $cities = City::where('is_active', true)->with('areas')->orderBy('name')->get();

        // Default price per city, plus each client's custom overrides (client_id => [city_id => price]).
        $defaultDeliveryPrices = $cities->mapWithKeys(fn (City $city) => [$city->id => (float) $city->delivery_price]);
        $clientDeliveryPrices = $clients->mapWithKeys(fn (ClientProfile $client) => [
            $client->id => $client->deliveryPrices->mapWithKeys(fn ($p) => [$p->city_id => (float) $p->delivery_price]),
        ]);

        return view('admin.orders.create', compact('clients', 'cities', 'defaultDeliveryPrices', 'clientDeliveryPrices'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasAdminAction('orders.add'), 403);

        $validated = $request->validate([
            'client_profile_id'        => 'required|exists:client_profiles,id',
            'order_description'        => 'nullable|string|max:255',
            'payment_type'             => 'required|in:cod,prepaid',
            'delivery_on_customer'     => 'nullable|boolean',
            'delivery_customer_amount' => 'nullable|required_if:delivery_on_customer,1|numeric|min:0',
            'order_price'              => 'nullable|required_if:payment_type,cod|numeric|min:0',
            'receiver_name'            => 'required|string|max:255',
            'receiver_phone'           => 'required|string|max:20',
            'city_id'                  => 'required|exists:cities,id',
            'area_id'                  => 'required|exists:areas,id',
            'address_text'             => 'required|string',
            'notes'                    => 'nullable|string',
            'batch_number'             => 'nullable|string|max:60',
            'delivery_shift'           => 'nullable|string|in:doesnt_matter,before_12pm,after_12pm',
        ]);

        $validated['delivery_shift'] = $validated['delivery_shift'] ?? 'doesnt_matter';

        $order = $this->orderService->createOrder($validated, Auth::user());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', __('Order :number created successfully.', ['number' => $order->order_number]));
    }

    public function show(Order $order)
    {
        $order->load(['clientProfile.masterUser', 'driverProfile.user', 'receiver.city', 'receiver.area', 'rejectionReason', 'trackingLogs.user', 'financialLedgerEntries.recordedBy']);

        $drivers = User::where('role', 'driver')->where('status', 'active')->orderBy('name')->get();
        $rejectionReasons = RejectionReason::where('is_active', true)->orderBy('reason')->get();

        return view('admin.orders.show', compact('order', 'drivers', 'rejectionReasons'));
    }

    public function edit(Order $order)
    {
        $order->load(['receiver', 'payment']);
        $cities = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();
        $clients = ClientProfile::where('status', 'active')->orderBy('company_name')->get();
        $rejectionReasons = RejectionReason::where('is_active', true)->orderBy('reason')->get();
        $orderStatuses = self::ORDER_STATUSES;
        $paymentStatuses = self::PAYMENT_STATUSES;

        return view('admin.orders.edit', compact('order', 'cities', 'clients', 'rejectionReasons', 'orderStatuses', 'paymentStatuses'));
    }

    public function update(Request $request, Order $order)
    {
        abort_unless($request->user()->hasAdminAction('orders.edit'), 403);

        // Check if updating order details
        if ($request->filled('receiver_name')) {
            $validated = $request->validate([
                'client_profile_id'        => 'required|exists:client_profiles,id',
                'order_description'        => 'nullable|string|max:255',
                'payment_type'             => 'required|in:cod,prepaid',
                'delivery_on_customer'     => 'nullable|boolean',
                'delivery_customer_amount' => 'nullable|required_if:delivery_on_customer,1|numeric|min:0',
                'order_price'              => 'nullable|required_if:payment_type,cod|numeric|min:0',
                'receiver_name'            => 'required|string|max:255',
                'receiver_phone'           => 'required|string|max:20',
                'city_id'                  => 'required|exists:cities,id',
                'area_id'                  => 'required|exists:areas,id',
                'address_text'             => 'required|string',
                'notes'                    => 'nullable|string',
                'delivery_shift'           => 'nullable|string|in:doesnt_matter,before_12pm,after_12pm',
                'status'                   => 'required|in:' . implode(',', self::ORDER_STATUSES),
                'payment_status'           => 'required|in:' . implode(',', self::PAYMENT_STATUSES),
                'rejection_reason_id'      => 'nullable|required_if:status,rejected|exists:rejection_reasons,id',
                'cancellation_reason'      => 'nullable|string|max:255',
            ]);

            $deliveryOnCustomer = $request->boolean('delivery_on_customer');

            $clientProfile = ClientProfile::find($validated['client_profile_id']);
            $cityChanged = $order->receiver->city_id != (int) $validated['city_id'] || $order->client_profile_id != (int) $validated['client_profile_id'];
            $clientDeliveryAmount = $cityChanged
                ? $clientProfile->getDeliveryPriceForCity((int) $validated['city_id'])
                : $order->payment->client_delivery_amount;

            $order->update([
                'client_profile_id' => $validated['client_profile_id'],
                'order_description' => $validated['order_description'] ?? null,
                'notes'             => $validated['notes'] ?? null,
                'delivery_shift'    => $validated['delivery_shift'] ?? 'doesnt_matter',
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

            $this->applyManualStatuses($order, $validated, Auth::user());

            return redirect()->route('admin.orders.show', $order)
                ->with('success', __('Order details updated successfully.'));
        }

        // Status or driver update
        $validated = $request->validate([
            'status'              => 'required|in:pending,assigned,picked_up,delivered,rejected,returned,cancelled',
            'driver_id'           => 'nullable|exists:users,id',
            'rejection_reason_id' => 'nullable|required_if:status,rejected|exists:rejection_reasons,id',
            'notes'               => 'nullable|string',
        ]);

        // The driver cannot be reassigned once an order has reached a locked status.
        if ($request->filled('driver_id') && in_array($order->status, self::DRIVER_LOCKED_STATUSES, true)) {
            return redirect()->back()->with('error', __('Driver cannot be reassigned: order #:number is already :status.', [
                'number' => $order->order_number,
                'status' => __(ucfirst($order->status)),
            ]));
        }

        // Auto-promote to assigned when a driver is assigned to a pending or assigned order
        if ($request->filled('driver_id') && ($validated['status'] === 'pending' || $validated['status'] === 'assigned')) {
            $validated['status'] = 'assigned';
        }

        $extra = [];
        if ($request->has('driver_id')) {
            $extra['driver_id'] = $request->input('driver_id');
        }
        if ($request->input('status') === 'rejected') {
            $extra['rejection_reason_id'] = $request->input('rejection_reason_id');
            $extra['notes'] = $request->input('notes');
        }

        if ($request->input('status') === 'delivered') {
            if ($request->hasFile('signature')) {
                $extra['signature_path'] = $request->file('signature')->store('signatures', 'public');
            }
            if ($request->hasFile('proof_image')) {
                $extra['proof_image_path'] = $request->file('proof_image')->store('proofs', 'public');
            }
        }

        $oldDriverProfileId = $order->driver_profile_id;

        $this->orderService->updateStatus($order, $validated['status'], $extra, Auth::user());

        if ($request->filled('driver_id')) {
            $newDriverProfile = DriverProfile::where('user_id', $validated['driver_id'])->first();
            $newDriverProfileId = $newDriverProfile?->id;

            if ($newDriverProfileId && $newDriverProfileId !== $oldDriverProfileId) {
                app(SupportNotificationService::class)->notifyOrdersAssigned(
                    $validated['driver_id'],
                    [$order->id],
                    Auth::id()
                );
            }
        }

        return redirect()->back()->with('success', __('Order updated successfully.'));
    }

    public function assignDriver(Request $request)
    {
        abort_unless($request->user()->hasAdminAction('orders.assign_driver'), 403);

        $validated = $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'driver_id'   => 'required|exists:users,id',
        ]);

        $orders = Order::whereIn('id', $validated['order_ids'])
            ->whereIn('status', ['pending', 'assigned'])
            ->get();

        $assignedOrderIds = [];
        foreach ($orders as $order) {
            $this->orderService->updateStatus(
                $order,
                'assigned',
                ['driver_id' => $validated['driver_id']],
                Auth::user()
            );
            $assignedOrderIds[] = $order->id;
        }

        $assigned = count($assignedOrderIds);

        if ($assigned > 0) {
            app(SupportNotificationService::class)->notifyOrdersAssigned(
                $validated['driver_id'],
                $assignedOrderIds,
                Auth::id()
            );
        }

        $skipped = count($validated['order_ids']) - $assigned;
        $msg = __(':count order(s) assigned to driver and marked as Picked Up.', ['count' => $assigned]);
        if ($skipped > 0) {
            $msg .= ' ' . __(':count order(s) skipped (not pending).', ['count' => $skipped]);
        }

        return redirect()->back()->with('success', $msg);
    }

    public function destroy(Order $order)
    {
        abort_unless(auth()->user()->hasAdminAction('orders.delete'), 403);

        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', __('Order deleted successfully.'));
    }

    public function calculatePrice(Request $request)
    {
        $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'city_id'           => 'required|exists:cities,id',
        ]);

        $client = ClientProfile::find($request->input('client_profile_id'));
        $price = $client->getDeliveryPriceForCity((int) $request->input('city_id'));

        return response()->json([
            'success' => true,
            'price'   => $price,
        ]);
    }

    /**
     * Apply the order / payment statuses set manually from the edit page.
     *
     * Order status changes go through OrderService so tracking logs, ledger
     * entries and notifications stay consistent. The payment status is only
     * overridden when the admin actually changed it, so it doesn't clobber
     * the value derived from the new order status (e.g. on delivery).
     */
    private function applyManualStatuses(Order $order, array $validated, User $actor): void
    {
        $originalPaymentStatus = $order->payment_status;

        if ($validated['status'] !== $order->status) {
            $extra = match ($validated['status']) {
                'rejected'  => ['rejection_reason_id' => $validated['rejection_reason_id']],
                'cancelled' => array_filter(['cancellation_reason' => $validated['cancellation_reason'] ?? null]),
                default     => [],
            };

            $this->orderService->updateStatus($order->refresh(), $validated['status'], $extra, $actor);
        } elseif ($order->status === 'rejected' && (int) $validated['rejection_reason_id'] !== $order->rejection_reason_id) {
            $order->update(['rejection_reason_id' => $validated['rejection_reason_id']]);
        } elseif ($order->status === 'cancelled' && ($validated['cancellation_reason'] ?? null) !== $order->cancellation_reason) {
            $order->update(['cancellation_reason' => $validated['cancellation_reason'] ?? null]);
        }

        if ($validated['payment_status'] !== $originalPaymentStatus) {
            $order->update(['payment_status' => $validated['payment_status']]);

            OrderTrackingLog::create([
                'order_id'    => $order->id,
                'user_id'     => $actor->id,
                'from_status' => $order->status,
                'to_status'   => $order->status,
                'description' => "Payment status manually changed from {$originalPaymentStatus} to {$validated['payment_status']}.",
            ]);
        }
    }

    private function getFilteredQuery(Request $request, $withRelations = true, $withStatus = true)
    {
        $query = Order::query();

        if ($withRelations) {
            $query->with(['clientProfile', 'driverProfile.user', 'receiver.city', 'receiver.area', 'payment']);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('receiver', fn ($rq) => $rq
                      ->where('receiver_name', 'like', "%{$search}%")
                      ->orWhere('receiver_phone', 'like', "%{$search}%")
                  );
            });
        }

        if ($withStatus && $request->filled('status')) {
            $status = $request->input('status');
            if (is_string($status) && isset(self::STATUS_GROUPS[$status])) {
                $query->whereIn('status', self::STATUS_GROUPS[$status]);
            } else {
                $query->where('status', $status);
            }
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        if ($request->filled('client_profile_id')) {
            $query->where('client_profile_id', $request->input('client_profile_id'));
        }
        if ($request->filled('driver_id')) {
            $driverProfile = DriverProfile::where('user_id', $request->input('driver_id'))->first();
            if ($driverProfile) {
                $query->where('driver_profile_id', $driverProfile->id);
            }
        }
        if ($request->filled('city_id')) {
            $query->whereHas('receiver', fn ($rq) => $rq->where('city_id', $request->input('city_id')));
        }

        return $query;
    }
}
