<?php

namespace App\Http\Controllers\Client;

use App\Models\ClientDeliveryInvoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $this->getClientProfile();

        $invoices = ClientDeliveryInvoice::where('client_profile_id', $profile->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.billing.index', compact('invoices'));
    }

    public function show(ClientDeliveryInvoice $invoice): View
    {
        $profile = $this->getClientProfile();

        abort_if((int) $invoice->client_profile_id !== $profile->id, 403);

        $invoice->load('orders.payment', 'orders.receiver.city');

        return view('client.billing.show', compact('invoice'));
    }
}
