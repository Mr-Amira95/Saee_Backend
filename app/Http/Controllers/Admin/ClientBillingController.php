<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryInvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\ClientDeliveryInvoice;
use App\Models\ClientProfile;
use App\Services\ClientDeliveryBillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientBillingController extends Controller
{
    public function __construct(private ClientDeliveryBillingService $service) {}

    public function index(Request $request)
    {
        $invoices = ClientDeliveryInvoice::with('clientProfile')
            ->when($request->client_id, fn($q, $id) => $q->where('client_profile_id', $id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $clients = ClientProfile::orderBy('company_name')->get();

        return view('admin.billing.index', compact('invoices', 'clients'));
    }

    public function create(ClientProfile $client)
    {
        return view('admin.billing.create', compact('client'));
    }

    public function store(Request $request, ClientProfile $client)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'notes'        => 'nullable|string',
        ]);

        $invoice = $this->service->generateDraftInvoice(
            $client,
            Carbon::parse($data['period_start']),
            Carbon::parse($data['period_end']),
            auth()->user()
        );

        return redirect()->route('admin.billing.show', $invoice)
            ->with('success', "Draft invoice created with {$invoice->billable_orders} billable orders.");
    }

    public function show(ClientDeliveryInvoice $invoice)
    {
        $invoice->load('clientProfile.user', 'orders.payment', 'createdBy');
        return view('admin.billing.show', compact('invoice'));
    }

    public function issue(Request $request, ClientDeliveryInvoice $invoice)
    {
        $data = $request->validate([
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        $this->service->issueInvoice(
            $invoice,
            isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
            auth()->user()
        );

        return back()->with('success', 'Invoice issued.');
    }

    public function pay(Request $request, ClientDeliveryInvoice $invoice)
    {
        $data = $request->validate([
            'payment_method'   => ['required', Rule::in(['bank_transfer', 'cash', 'cliq'])],
            'reference_number' => 'nullable|string|max:100',
        ]);

        $this->service->recordPayment(
            $invoice,
            $data['payment_method'],
            $data['reference_number'] ?? null,
            auth()->user()
        );

        return back()->with('success', 'Payment recorded. Invoice marked as paid.');
    }

    public function destroy(ClientDeliveryInvoice $invoice)
    {
        abort_if($invoice->status !== DeliveryInvoiceStatus::Draft, 403, 'Only draft invoices can be deleted.');

        $invoice->delete();

        return redirect()->route('admin.billing.index')
            ->with('success', 'Draft invoice deleted.');
    }
}
