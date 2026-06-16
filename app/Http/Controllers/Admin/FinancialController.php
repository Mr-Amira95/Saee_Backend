<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ClientProfile;
use App\Models\FinancialLedgerEntry;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;

class FinancialController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Financial dashboard.
     */
    public function index(Request $request)
    {
        // 1. Driver Balances (how much cash each driver currently holds)
        $driverBalances = User::where('role', 'driver')
            ->where('status', 'active')
            ->get()
            ->map(function ($driver) {
                // Cash collected by driver (to driver) minus settled cash (from driver)
                $collected = FinancialLedgerEntry::where('driver_id', $driver->id)
                    ->where('to_account', 'driver')
                    ->sum('amount');

                $settled = FinancialLedgerEntry::where('driver_id', $driver->id)
                    ->where('from_account', 'driver')
                    ->sum('amount');

                $balance = $collected - $settled;

                // Also count pending orders to settle
                $pendingOrdersCount = Order::where('driver_id', $driver->id)
                    ->where('status', 'delivered')
                    ->where('payment_status', 'with_driver')
                    ->count();

                return [
                    'driver' => $driver,
                    'balance' => $balance,
                    'pending_orders_count' => $pendingOrdersCount
                ];
            })->filter(fn($d) => $d['balance'] > 0 || $d['pending_orders_count'] > 0);

        // 2. Client Balances (Calculated Payout Balances)
        $clientBalances = ClientProfile::orderBy('company_name')
            ->get()
            ->map(function ($client) {
                // COD collected by company/drivers for this client (to_account='driver' OR to_account='company')
                $codCollected = FinancialLedgerEntry::where('client_profile_id', $client->id)
                    ->where('type', 'cod_collection')
                    ->sum('amount');

                // Customer delivery collections (offsets shipping charges)
                $deliveryCollected = FinancialLedgerEntry::where('client_profile_id', $client->id)
                    ->where('type', 'delivery_collection')
                    ->sum('amount');

                // Shipping fees charged to this client
                $shippingCharges = FinancialLedgerEntry::where('client_profile_id', $client->id)
                    ->where('type', 'shipping_charge')
                    ->sum('amount');

                // Payouts already paid to this client (reimbursements & COD cash transfers)
                $payoutsMade = FinancialLedgerEntry::where('client_profile_id', $client->id)
                    ->where('to_account', 'client')
                    ->sum('amount');

                // Net payout balance due to client:
                // COD collected + Delivery reimbursement - Shipping fees - Payouts made
                $netBalanceDue = ($codCollected + $deliveryCollected) - ($shippingCharges + $payoutsMade);

                $pendingPayoutCount = Order::where('client_profile_id', $client->id)
                    ->where('status', 'delivered')
                    ->where('payment_type', 'cod')
                    ->where('payment_status', '!=', 'paid')
                    ->count();

                return [
                    'client' => $client,
                    'cod_collected' => $codCollected,
                    'shipping_charges' => $shippingCharges,
                    'payouts_made' => $payoutsMade,
                    'net_balance_due' => $netBalanceDue,
                    'pending_payout_count' => $pendingPayoutCount
                ];
            })->filter(fn($c) => abs($c['net_balance_due']) > 0 || $c['pending_payout_count'] > 0);

        // 3. System totals
        $totalDriverCash = FinancialLedgerEntry::where('to_account', 'driver')->sum('amount') 
            - FinancialLedgerEntry::where('from_account', 'driver')->sum('amount');

        $totalClientPayoutsDue = $clientBalances->sum('net_balance_due');

        return view('admin.financials.index', compact('driverBalances', 'clientBalances', 'totalDriverCash', 'totalClientPayoutsDue'));
    }

    /**
     * Driver cash settlement form.
     */
    public function driverSettlementForm(User $driver)
    {
        $orders = Order::where('driver_id', $driver->id)
            ->where('status', 'delivered')
            ->where('payment_status', 'with_driver')
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalCash = 0;
        foreach ($orders as $order) {
            $order->cash_held = FinancialLedgerEntry::where('order_id', $order->id)
                ->where('driver_id', $driver->id)
                ->where('to_account', 'driver')
                ->sum('amount');
            $totalCash += $order->cash_held;
        }

        return view('admin.financials.settle_driver', compact('driver', 'orders', 'totalCash'));
    }

    /**
     * Process driver settlement.
     */
    public function settleDriver(Request $request, User $driver)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'exists:orders,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $settledCount = $this->orderService->settleDriverCash(
            $driver,
            $request->input('orders'),
            Auth::user(),
            $request->input('reference_number'),
            $request->input('notes')
        );

        return redirect()->route('admin.financials.index')
            ->with('success', "Successfully settled {$settledCount} orders from driver {$driver->name}.");
    }

    /**
     * Client payout form.
     */
    public function clientPayoutForm(ClientProfile $client)
    {
        $orders = Order::where('client_profile_id', $client->id)
            ->where('status', 'delivered')
            ->where('payment_type', 'cod')
            ->where('payment_status', '!=', 'paid')
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalPayout = 0;
        $totalShipping = 0;
        
        foreach ($orders as $order) {
            // COD Amount collected
            $order->cod_amount = $order->order_price;
            
            // Shipping fee (only subtract if client paid for delivery)
            $order->shipping_fee = $order->delivery_on_customer ? 0 : $order->delivery_amount;
            
            // Total net payout per order
            $order->net_payout = $order->cod_amount - $order->shipping_fee;

            $totalPayout += $order->cod_amount;
            $totalShipping += $order->shipping_fee;
        }

        $netPayoutAmount = $totalPayout - $totalShipping;

        return view('admin.financials.payout_client', compact('client', 'orders', 'totalPayout', 'totalShipping', 'netPayoutAmount'));
    }

    /**
     * Process client payout.
     */
    public function payoutClient(Request $request, ClientProfile $client)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'exists:orders,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $payoutCount = $this->orderService->payoutClient(
            $client,
            $request->input('orders'),
            Auth::user(),
            $request->input('reference_number'),
            $request->input('notes')
        );

        return redirect()->route('admin.financials.index')
            ->with('success', "Successfully processed COD payout for {$payoutCount} orders to client {$client->company_name}.");
    }

    /**
     * View client invoices.
     */
    public function invoices(Request $request)
    {
        $query = Invoice::with('clientProfile');

        if ($request->filled('client_id')) {
            $query->where('client_profile_id', $request->input('client_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('clientProfile', function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%");
                });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $clients = ClientProfile::orderBy('company_name')->get();

        return view('admin.financials.invoices', compact('invoices', 'clients'));
    }

    /**
     * View specific invoice layout.
     */
    public function showInvoice(Invoice $invoice)
    {
        $invoice->load('clientProfile.city', 'clientProfile.area', 'payoutLedgerEntry');
        
        $ref = $invoice->payoutLedgerEntry->reference_number;
        
        if ($ref) {
            $orders = Order::where('client_profile_id', $invoice->client_profile_id)
                ->whereHas('financialLedgerEntries', function ($q) use ($ref) {
                    $q->where('type', 'client_payout')->where('reference_number', $ref);
                })
                ->get();
        } else {
            $orders = Order::where('client_profile_id', $invoice->client_profile_id)
                ->where('payment_status', 'paid')
                ->whereDate('updated_at', $invoice->created_at->toDateString())
                ->get();
        }

        return view('admin.financials.invoice_show', compact('invoice', 'orders'));
    }

    /**
     * Financial Reconciliation.
     */
    public function reconciliation(Request $request)
    {
        $drivers = User::where('role', 'driver')->get()->map(function ($driver) {
            $collected = FinancialLedgerEntry::where('driver_id', $driver->id)
                ->where('to_account', 'driver')
                ->sum('amount');

            $settled = FinancialLedgerEntry::where('driver_id', $driver->id)
                ->where('from_account', 'driver')
                ->sum('amount');

            $cashHeld = $collected - $settled;

            $undeliveredCashHeld = Order::where('driver_id', $driver->id)
                ->where('status', '!=', 'delivered')
                ->where('payment_status', 'with_driver')
                ->count();

            return [
                'driver' => $driver,
                'collected' => $collected,
                'settled' => $settled,
                'cash_held' => $cashHeld,
                'mismatch' => $undeliveredCashHeld > 0 || $cashHeld < 0
            ];
        });

        $totalCollected = FinancialLedgerEntry::where('to_account', 'driver')->sum('amount');
        $totalSettled = FinancialLedgerEntry::where('from_account', 'driver')->sum('amount');
        $netDiscrepancy = $totalCollected - $totalSettled;

        return view('admin.financials.reconciliation', compact('drivers', 'totalCollected', 'totalSettled', 'netDiscrepancy'));
    }
}
