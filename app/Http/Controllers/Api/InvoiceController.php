<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Determine Client Profile ID based on master or employee
        $clientProfileId = null;
        if ($user->isClientMaster()) {
            $clientProfileId = $user->clientProfile?->id;
        } elseif ($user->isClientEmployee()) {
            $clientProfileId = $user->clientEmployee?->client_profile_id;
        }

        if (!$clientProfileId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Client profile not found.',
            ], 403);
        }

        $query = Invoice::where('client_profile_id', $clientProfileId);

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
        $codCollected = (float) $statsQuery->sum('cod_amount');
        $customerDelivery = (float) $statsQuery->sum('customer_delivery_amount');

        $invoices = $query->latest()
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Invoices retrieved successfully.',
            'statistics' => [
                'cod_collected' => $codCollected,
                'customer_delivery' => $customerDelivery,
            ],
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        ]);
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Determine Client Profile ID based on master or employee
        $clientProfileId = null;
        if ($user->isClientMaster()) {
            $clientProfileId = $user->clientProfile?->id;
        } elseif ($user->isClientEmployee()) {
            $clientProfileId = $user->clientEmployee?->client_profile_id;
        }

        if (!$clientProfileId || (int) $invoice->client_profile_id !== $clientProfileId) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not own this invoice.',
            ], 403);
        }

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

        // Format orders details
        $formattedOrders = $orders->map(fn($o) => [
            'order_number' => $o->order_number,
            'recipient_name' => $o->receiver?->receiver_name,
            'recipient_phone' => $o->receiver?->receiver_phone,
            'city' => $o->receiver?->city?->name,
            'area' => $o->receiver?->area?->name,
            'payment_type' => $o->payment?->payment_type,
            'cod_amount' => $o->payment?->order_amount ? (float) $o->payment->order_amount : 0,
            'customer_delivery' => $o->payment?->delivery_on_customer ? (float) ($o->payment->customer_delivery_amount ?? 0) : 0,
            'net_payout' => (float) ($o->payment?->order_amount ?? 0) + ($o->payment?->delivery_on_customer ? (float) ($o->payment->customer_delivery_amount ?? 0) : 0),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice details retrieved successfully.',
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_profile' => [
                    'id' => $invoice->clientProfile->id,
                    'company_name' => $invoice->clientProfile->company_name,
                    'email' => $invoice->clientProfile->email,
                    'phone' => $invoice->clientProfile->masterUser?->phone,
                    'address' => $invoice->clientProfile->address_line1,
                    'city' => $invoice->clientProfile->city?->name,
                ],
                'payment_info' => [
                    'method' => 'Direct Transfer / Payout Cash',
                    'reference_number' => $invoice->payoutLedgerEntry->reference_number ?? null,
                    'ledger_ref_id' => $invoice->payout_ledger_entry_id,
                    'recorded_by' => $invoice->payoutLedgerEntry->recordedBy->name ?? 'System',
                ],
                'created_at' => $invoice->created_at->toDateTimeString(),
                'status' => $invoice->status,
                'total_orders' => $invoice->total_orders,
                'cod_amount' => (float) $invoice->cod_amount,
                'shipping_amount' => (float) $invoice->shipping_amount,
                'customer_delivery_amount' => (float) $invoice->customer_delivery_amount,
                'net_amount' => (float) $invoice->net_amount,
                'notes' => $invoice->notes,
            ],
            'orders' => $formattedOrders,
        ]);
    }
}
