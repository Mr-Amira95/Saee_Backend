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
            ->latest()
            ->paginate(20);

        return view('client.billing.index', compact('invoices'));
    }

    public function show(ClientDeliveryInvoice $invoice): View
    {
        $profile = $this->getClientProfile();

        abort_if((int) $invoice->client_profile_id !== $profile->id, 403);

        $invoice->load('orders.payment', 'orders.receiver.city');

        return view('client.billing.show', compact('invoice'));
    }

    public function pay(Request $request, ClientDeliveryInvoice $invoice, \App\Services\ClientDeliveryBillingService $service)
    {
        $profile = $this->getClientProfile();

        abort_if((int) $invoice->client_profile_id !== $profile->id, 403);
        abort_if(!in_array($invoice->status->value ?? $invoice->status, ['issued', 'overdue']), 400, 'Only issued or overdue invoices can be marked as paid.');

        $data = $request->validate([
            'payment_method'   => ['required', \Illuminate\Validation\Rule::in(['bank_transfer', 'cash', 'cliq'])],
            'reference_number' => 'nullable|string|max:100',
        ]);

        $service->recordPayment(
            $invoice,
            $data['payment_method'],
            $data['reference_number'] ?? null,
            auth()->user()
        );

        return back()->with('success', __('Payment recorded. Invoice marked as paid.'));
    }
}
