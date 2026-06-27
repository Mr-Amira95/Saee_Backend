<?php

namespace App\Http\Controllers\Client;

use App\Models\ClientDeliveryInvoice;
use App\Models\FinancialLedgerEntry;
use App\Models\Invoice;
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

        $invoices = Invoice::where('client_profile_id', $profile->id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.finances.invoices', compact('invoices'));
    }
}
