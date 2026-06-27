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
    public function invoices(Request $request): View
    {
        $profile = $this->getClientProfile();

        $query = Invoice::where('client_profile_id', $profile->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        // Calculate statistics based on filtered query
        $statsQuery = clone $query;
        $codCollected    = (float) $statsQuery->sum('cod_amount');
        $customerDelivery = (float) $statsQuery->sum('customer_delivery_amount');
        $shippingCharges = (float) $statsQuery->sum('shipping_amount');

        $invoices = $query->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.finances.invoices', compact(
            'invoices',
            'codCollected',
            'customerDelivery',
            'shippingCharges'
        ));
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
