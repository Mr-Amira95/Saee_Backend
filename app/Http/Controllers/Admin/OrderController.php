<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ClientProfile;
use App\Models\City;
use App\Models\User;
use App\Models\RejectionReason;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['clientProfile', 'driver', 'city', 'area']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('receiver_name', 'like', "%{$search}%")
                  ->orWhere('receiver_phone', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        if ($request->filled('client_profile_id')) {
            $query->where('client_profile_id', $request->input('client_profile_id'));
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->input('driver_id'));
        }
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->input('city_id'));
        }
        if ($request->filled('batch_number')) {
            $query->where('batch_number', $request->input('batch_number'));
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Data for filters
        $clients = ClientProfile::orderBy('company_name')->get();
        $drivers = User::where('role', 'driver')->where('status', 'active')->orderBy('name')->get();
        $cities = City::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'pending' => Order::where('status', 'pending')->count(),
            'picked_up' => Order::where('status', 'picked_up')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'with_driver' => Order::where('payment_status', 'with_driver')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'clients', 'drivers', 'cities', 'stats'));
    }

    public function create()
    {
        $clients = ClientProfile::where('status', 'active')->orderBy('company_name')->get();
        $drivers = User::where('role', 'driver')->where('status', 'active')->orderBy('name')->get();
        $cities = City::where('is_active', true)->with('areas')->orderBy('name')->get();

        return view('admin.orders.create', compact('clients', 'drivers', 'cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'driver_id'         => 'nullable|exists:users,id',
            'order_description' => 'nullable|string|max:255',
            'payment_type'      => 'required|in:cod,prepaid',
            'delivery_on_customer' => 'nullable|boolean',
            'delivery_customer_amount' => 'nullable|required_if:delivery_on_customer,1|numeric|min:0',
            'order_price'       => 'nullable|required_if:payment_type,cod|numeric|min:0',
            'receiver_name'     => 'required|string|max:255',
            'receiver_phone'    => 'required|string|max:20',
            'city_id'           => 'required|exists:cities,id',
            'area_id'           => 'required|exists:areas,id',
            'address_text'      => 'required|string',
            'address_location'  => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
            'batch_number'      => 'nullable|string|max:60',
        ]);

        $order = $this->orderService->createOrder($validated, Auth::user());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', "Order {$order->order_number} created successfully.");
    }

    public function show(Order $order)
    {
        $order->load(['clientProfile.masterUser', 'driver', 'city', 'area', 'rejectionReason', 'trackingLogs.user', 'financialLedgerEntries.recordedBy']);
        
        $drivers = User::where('role', 'driver')->where('status', 'active')->orderBy('name')->get();
        $rejectionReasons = RejectionReason::where('is_active', true)->orderBy('reason')->get();

        return view('admin.orders.show', compact('order', 'drivers', 'rejectionReasons'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,picked_up,delivered,rejected,returned,cancelled',
            'driver_id' => 'nullable|exists:users,id',
            'rejection_reason_id' => 'nullable|required_if:status,rejected|exists:rejection_reasons,id',
            'notes' => 'nullable|string',
        ]);

        $extra = [];
        if ($request->has('driver_id')) {
            $extra['driver_id'] = $request->input('driver_id');
        }
        if ($request->input('status') === 'rejected') {
            $extra['rejection_reason_id'] = $request->input('rejection_reason_id');
            $extra['notes'] = $request->input('notes');
        }

        // Mock upload signature/proof if simulating mobile delivery
        if ($request->input('status') === 'delivered') {
            if ($request->hasFile('signature')) {
                $extra['signature_path'] = $request->file('signature')->store('signatures', 'public');
            }
            if ($request->hasFile('proof_image')) {
                $extra['proof_image_path'] = $request->file('proof_image')->store('proofs', 'public');
            }
        }

        $this->orderService->updateStatus($order, $request->input('status'), $extra, Auth::user());

        return redirect()->back()->with('success', 'Order updated successfully.');
    }

    public function assignDriver(Request $request)
    {
        $validated = $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'driver_id'   => 'required|exists:users,id',
        ]);

        $orders = Order::whereIn('id', $validated['order_ids'])
            ->where('status', 'pending')
            ->get();

        $assigned = 0;
        foreach ($orders as $order) {
            $this->orderService->updateStatus(
                $order,
                'picked_up',
                ['driver_id' => $validated['driver_id']],
                Auth::user()
            );
            $assigned++;
        }

        $skipped = count($validated['order_ids']) - $assigned;
        $msg = "{$assigned} order(s) assigned to driver and marked as Picked Up.";
        if ($skipped > 0) {
            $msg .= " {$skipped} order(s) skipped (not pending).";
        }

        return redirect()->back()->with('success', $msg);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }

    /**
     * Ajax route to get dynamic delivery price calculation.
     */
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'client_profile_id' => 'required|exists:client_profiles,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        $client = ClientProfile::find($request->input('client_profile_id'));
        $price = $client->getDeliveryPriceForCity((int)$request->input('city_id'));

        return response()->json([
            'success' => true,
            'price' => $price
        ]);
    }
}
