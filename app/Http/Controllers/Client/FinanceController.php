<?php

namespace App\Http\Controllers\Client;

use App\Models\ClientDeliveryInvoice;
use App\Models\FinancialLedgerEntry;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $this->getClientProfile();

        $ledgerQuery = FinancialLedgerEntry::where('client_profile_id', $profile->id)
            ->with('order');

        if ($request->filled('type')) {
            $ledgerQuery->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $ledgerQuery->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $ledgerQuery->whereDate('created_at', '<=', $request->to);
        }

        $ledger = $ledgerQuery->latest()->paginate(20)->withQueryString();

        $baseQuery = FinancialLedgerEntry::where('client_profile_id', $profile->id);
        $codCollected    = (float) (clone $baseQuery)->where('type', 'cod_collection')->sum('amount');
        $shippingCharges = (float) (clone $baseQuery)->where('type', 'shipping_charge')->sum('amount');
        $payoutsReceived = (float) (clone $baseQuery)->where('type', 'client_payout')->sum('amount');
        $netBalanceDue   = $codCollected - $shippingCharges - $payoutsReceived;

        return view('client.finances.index', compact(
            'profile', 'ledger',
            'codCollected', 'shippingCharges', 'payoutsReceived', 'netBalanceDue'
        ));
    }

    public function invoices(Request $request): View
    {
        $profile = $this->getClientProfile();

        $query = Invoice::where('client_profile_id', $profile->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        $invoices = $query->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.finances.invoices', compact('invoices'));
    }

    public function showInvoice(Invoice $invoice): View
    {
        $profile = $this->getClientProfile();

        abort_if((int) $invoice->client_profile_id !== $profile->id, 403);

        $invoice->load('clientProfile.city', 'clientProfile.area', 'payoutLedgerEntry');

        $ref = $invoice->payoutLedgerEntry?->reference_number;

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

        return view('client.finances.invoice_show', compact('invoice', 'orders'));
    }
}
