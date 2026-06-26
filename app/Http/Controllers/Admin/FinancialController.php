<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
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

    public function index(Request $request)
    {
        $driverBalances = User::where('role', 'driver')
            ->where('status', 'active')
            ->get()
            ->map(function ($driver) {
                $collected = FinancialLedgerEntry::where('driver_id', $driver->id)
                    ->where('to_account', 'driver')
                    ->sum('amount');

                $settled = FinancialLedgerEntry::where('driver_id', $driver->id)
                    ->where('from_account', 'driver')
                    ->sum('amount');

                $balance = $collected - $settled;

                $driverProfile = $driver->driverProfile;
                $pendingOrdersCount = $driverProfile
                    ? Order::where('driver_profile_id', $driverProfile->id)
                        ->where('status', 'delivered')
                        ->where('payment_status', 'with_driver')
                        ->count()
                    : 0;

                return [
                    'driver'               => $driver,
                    'balance'              => $balance,
                    'pending_orders_count' => $pendingOrdersCount,
                ];
            })->filter(fn ($d) => $d['balance'] > 0 || $d['pending_orders_count'] > 0);

        $clientBalances = ClientProfile::orderBy('company_name')
            ->get()
            ->map(function ($client) {
                $codCollected = FinancialLedgerEntry::where('client_profile_id', $client->id)
                    ->where('type', 'cod_collection')
                    ->sum('amount');

                $deliveryCollected = FinancialLedgerEntry::where('client_profile_id', $client->id)
                    ->where('type', 'delivery_collection')
                    ->sum('amount');

                $shippingCharges = FinancialLedgerEntry::where('client_profile_id', $client->id)
                    ->where('type', 'shipping_charge')
                    ->sum('amount');

                $payoutsMade = FinancialLedgerEntry::where('client_profile_id', $client->id)
                    ->where('to_account', 'client')
                    ->sum('amount');

                $netBalanceDue = ($codCollected + $deliveryCollected) - ($shippingCharges + $payoutsMade);

                $pendingPayoutCount = Order::where('client_profile_id', $client->id)
                    ->where('status', 'delivered')
                    ->whereHas('payment', fn ($pq) => $pq->where('payment_type', 'cod'))
                    ->whereIn('payment_status', ['with_driver', 'with_company'])
                    ->count();

                return [
                    'client'               => $client,
                    'cod_collected'        => $codCollected,
                    'shipping_charges'     => $shippingCharges,
                    'payouts_made'         => $payoutsMade,
                    'net_balance_due'      => $netBalanceDue,
                    'gross_payout_due'     => ($codCollected + $deliveryCollected) - $payoutsMade,
                    'pending_payout_count' => $pendingPayoutCount,
                ];
            })->filter(fn ($c) => abs($c['net_balance_due']) > 0 || $c['pending_payout_count'] > 0);

        $totalDriverCash = FinancialLedgerEntry::where('to_account', 'driver')->sum('amount')
            - FinancialLedgerEntry::where('from_account', 'driver')->sum('amount');

        $totalClientPayoutsDue = $clientBalances->sum('gross_payout_due');

        return view('admin.financials.index', compact('driverBalances', 'clientBalances', 'totalDriverCash', 'totalClientPayoutsDue'));
    }

    public function driverSettlementForm(User $driver)
    {
        $driverProfile = DriverProfile::where('user_id', $driver->id)->first();

        $orders = $driverProfile
            ? Order::where('driver_profile_id', $driverProfile->id)
                ->where('status', 'delivered')
                ->where('payment_status', 'with_driver')
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

        return view('admin.financials.settle_driver', compact('driver', 'orders', 'totalCash'));
    }

    public function settleDriver(Request $request, User $driver)
    {
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

        return redirect()->route('admin.financials.index')
            ->with('success', "Successfully settled {$settledCount} orders from driver {$driver->name}.");
    }

    public function clientPayoutForm(ClientProfile $client)
    {
        $orders = Order::where('client_profile_id', $client->id)
            ->where('status', 'delivered')
            ->whereHas('payment', fn ($pq) => $pq->where('payment_type', 'cod'))
            ->where('payment_status', 'with_company')
            ->with('payment')
            ->orderBy('updated_at', 'desc')
            ->get();

        $totalCod              = 0;
        $totalCustomerDelivery = 0;

        foreach ($orders as $order) {
            $payment = $order->payment;
            $order->cod_amount          = (float) ($payment->order_amount ?? 0);
            $order->customer_delivery   = $payment->delivery_on_customer ? (float) ($payment->customer_delivery_amount ?? 0) : 0;
            $order->net_payout          = $order->cod_amount + $order->customer_delivery;

            $totalCod              += $order->cod_amount;
            $totalCustomerDelivery += $order->customer_delivery;
        }

        $totalPayout     = $totalCod;
        $totalShipping   = $totalCustomerDelivery;
        $netPayoutAmount = $totalCod + $totalCustomerDelivery;

        return view('admin.financials.payout_client', compact('client', 'orders', 'totalPayout', 'totalShipping', 'netPayoutAmount'));
    }

    public function payoutClient(Request $request, ClientProfile $client)
    {
        $request->validate([
            'orders'           => 'required|array',
            'orders.*'         => 'exists:orders,id',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
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
        $clients  = ClientProfile::orderBy('company_name')->get();

        return view('admin.financials.invoices', compact('invoices', 'clients'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load('clientProfile.city', 'clientProfile.area', 'payoutLedgerEntry');

        $ref = $invoice->payoutLedgerEntry->reference_number;

        if ($ref) {
            $orders = Order::where('client_profile_id', $invoice->client_profile_id)
                ->whereHas('financialLedgerEntries', function ($q) use ($ref) {
                    $q->where('type', 'client_payout')->where('reference_number', $ref);
                })
                ->with('payment', 'receiver.city', 'receiver.area')
                ->get();
        } else {
            $orders = Order::where('client_profile_id', $invoice->client_profile_id)
                ->where('payment_status', 'paid')
                ->whereDate('updated_at', $invoice->created_at->toDateString())
                ->with('payment', 'receiver.city', 'receiver.area')
                ->get();
        }

        return view('admin.financials.invoice_show', compact('invoice', 'orders'));
    }

    public function reconciliation(Request $request)
    {
        $drivers = User::where('role', 'driver')->get()->map(function ($driver) {
            $collected = FinancialLedgerEntry::where('driver_id', $driver->id)
                ->where('to_account', 'driver')
                ->sum('amount');

            $settled = FinancialLedgerEntry::where('driver_id', $driver->id)
                ->where('from_account', 'driver')
                ->sum('amount');

            return [
                'driver'    => $driver,
                'collected' => $collected,
                'settled'   => $settled,
                'balance'   => $collected - $settled,
            ];
        });

        $totalCollected = $drivers->sum('collected');
        $totalSettled   = $drivers->sum('settled');
        $totalBalance   = $drivers->sum('balance');

        return view('admin.financials.reconciliation', compact('drivers', 'totalCollected', 'totalSettled', 'totalBalance'));
    }
}
