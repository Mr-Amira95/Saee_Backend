<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\HandoverRequest;
use App\Models\FinancialLedgerEntry;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HandoverRequestController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Handover page: cash still held by drivers, pending and approved handover requests.
     */
    public function index()
    {
        $driverBalances = User::where('role', 'driver')
            ->where('status', 'active')
            ->with('driverProfile')
            ->get()
            ->map(function ($driver) {
                $collected = FinancialLedgerEntry::where('driver_id', $driver->id)
                    ->where('to_account', 'driver')
                    ->sum('amount');

                $settled = FinancialLedgerEntry::where('driver_id', $driver->id)
                    ->where('from_account', 'driver')
                    ->sum('amount');

                $pendingOrdersCount = $driver->driverProfile
                    ? Order::where('driver_profile_id', $driver->driverProfile->id)
                        ->where('status', 'delivered')
                        ->where('payment_status', 'with_driver')
                        ->whereNull('handover_request_id')
                        ->count()
                    : 0;

                $pendingHandoverRequest = HandoverRequest::where('driver_id', $driver->id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();

                return [
                    'driver'                   => $driver,
                    'balance'                  => $collected - $settled,
                    'pending_orders_count'     => $pendingOrdersCount,
                    'pending_handover_request' => $pendingHandoverRequest,
                ];
            })->filter(fn ($d) => $d['balance'] > 0 || $d['pending_orders_count'] > 0 || $d['pending_handover_request']);

        $totalDriverCash = FinancialLedgerEntry::where('to_account', 'driver')->sum('amount')
            - FinancialLedgerEntry::where('from_account', 'driver')->sum('amount');

        $pendingRequests = HandoverRequest::where('status', 'pending')
            ->with('driver')
            ->latest()
            ->get();

        $approvedRequests = HandoverRequest::where('status', 'approved')
            ->with(['driver', 'approver'])
            ->latest()
            ->get();

        return view('admin.financials.handover_requests.index', compact('driverBalances', 'totalDriverCash', 'pendingRequests', 'approvedRequests'));
    }

    /**
     * Display the specified handover request details.
     */
    public function show(HandoverRequest $handoverRequest)
    {
        $handoverRequest->load(['driver', 'orders.payment', 'orders.rejectionReason', 'orders.clientProfile']);

        $deliveredOrders = $handoverRequest->orders()
            ->where('status', 'delivered')
            ->get();

        $rejectedOrders = $handoverRequest->orders()
            ->where('status', 'rejected')
            ->get();

        $totalCash = 0;
        foreach ($deliveredOrders as $order) {
            $order->cash_held = FinancialLedgerEntry::where('order_id', $order->id)
                ->where('driver_id', $handoverRequest->driver_id)
                ->where('to_account', 'driver')
                ->sum('amount');
            $totalCash += $order->cash_held;
        }

        return view('admin.financials.handover_requests.show', compact(
            'handoverRequest',
            'deliveredOrders',
            'rejectedOrders',
            'totalCash'
        ));
    }

    /**
     * Approve the specified handover request.
     */
    public function approve(HandoverRequest $handoverRequest)
    {
        abort_unless(auth()->user()->hasAdminAction('finances.handover'), 403);

        try {
            $this->orderService->approveHandover($handoverRequest, Auth::user());

            return redirect()->route('admin.financials.handover-requests.index', ['tab' => 'pending'])
                ->with('success', __('Handover request for driver :name has been successfully approved.', ['name' => $handoverRequest->driver->name]));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Manually settle the cash a driver is holding (orders not in a handover request).
     */
    public function settleForm(User $driver)
    {
        $driverProfile = DriverProfile::where('user_id', $driver->id)->first();

        $orders = $driverProfile
            ? Order::where('driver_profile_id', $driverProfile->id)
                ->where('status', 'delivered')
                ->where('payment_status', 'with_driver')
                ->whereNull('handover_request_id')
                ->with('payment')
                ->orderBy('updated_at', 'desc')
                ->get()
            : collect();

        $totalCash = 0;
        foreach ($orders as $order) {
            $order->cash_held = FinancialLedgerEntry::where('order_id', $order->id)
                ->where('driver_id', $driver->id)
                ->where('to_account', 'driver')
                ->sum('amount');
            $totalCash += $order->cash_held;
        }

        return view('admin.financials.handover_requests.settle_driver', compact('driver', 'orders', 'totalCash'));
    }

    public function settle(Request $request, User $driver)
    {
        abort_unless($request->user()->hasAdminAction('finances.handover'), 403);

        $request->validate([
            'orders'           => 'required|array',
            'orders.*'         => 'exists:orders,id',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        $settledCount = $this->orderService->settleDriverCash(
            $driver,
            $request->input('orders'),
            Auth::user(),
            $request->input('reference_number'),
            $request->input('notes')
        );

        return redirect()->route('admin.financials.handover-requests.index')
            ->with('success', __('Successfully settled :count orders from driver :name.', ['count' => $settledCount, 'name' => $driver->name]));
    }
}
