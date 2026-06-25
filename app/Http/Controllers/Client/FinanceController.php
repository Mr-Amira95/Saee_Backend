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

        $ledger          = $ledgerQuery->latest()->paginate(20)->withQueryString();
        $invoices        = Invoice::where('client_profile_id', $profile->id)->latest()->paginate(20)->withQueryString();
        $deliveryInvoices = ClientDeliveryInvoice::where('client_profile_id', $profile->id)->latest()->paginate(20)->withQueryString();
        $balance         = (float) ($profile->balance ?? 0);
        $creditLimit     = (float) ($profile->credit_limit ?? 0);

        return view('client.finances.index', compact('profile', 'ledger', 'invoices', 'deliveryInvoices', 'balance', 'creditLimit'));
    }
}
