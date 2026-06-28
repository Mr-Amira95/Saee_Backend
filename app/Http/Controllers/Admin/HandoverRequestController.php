<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HandoverRequest;
use App\Models\FinancialLedgerEntry;
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
     * Display a listing of the handover requests.
     */
    public function index()
    {
        $pendingRequests = HandoverRequest::where('status', 'pending')
            ->with('driver')
            ->latest()
            ->get();

        $approvedRequests = HandoverRequest::where('status', 'approved')
            ->with(['driver', 'approver'])
            ->latest()
            ->get();

        return view('admin.financials.handover_requests.index', compact('pendingRequests', 'approvedRequests'));
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
        try {
            $this->orderService->approveHandover($handoverRequest, Auth::user());

            return redirect()->route('admin.financials.handover-requests.index')
                ->with('success', "Handover request for driver {$handoverRequest->driver->name} has been successfully approved.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
