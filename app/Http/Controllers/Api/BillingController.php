<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientDeliveryInvoice;
use App\Services\ClientDeliveryBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Determine Client Profile ID
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

        $query = ClientDeliveryInvoice::where('client_profile_id', $clientProfileId);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->latest()
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Billing invoices retrieved successfully.',
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        ]);
    }

    public function show(Request $request, ClientDeliveryInvoice $invoice): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Determine Client Profile ID
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

        $invoice->load('orders.payment', 'orders.receiver.city');

        // Format orders details
        $formattedOrders = $invoice->orders->map(fn($o) => [
            'order_number' => $o->order_number,
            'recipient_name' => $o->receiver?->receiver_name,
            'recipient_phone' => $o->receiver?->receiver_phone,
            'city' => $o->receiver?->city?->name,
            'delivered_at' => $o->delivered_at?->toDateTimeString(),
            'delivery_fee' => $o->payment?->client_delivery_amount ? (float) $o->payment->client_delivery_amount : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Billing invoice details retrieved successfully.',
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_profile' => [
                    'id' => $invoice->clientProfile->id,
                    'company_name' => $invoice->clientProfile->company_name,
                    'email' => $invoice->clientProfile->email,
                    'phone' => $invoice->clientProfile->masterUser?->phone,
                ],
                'period_start' => $invoice->period_start->toDateString(),
                'period_end' => $invoice->period_end->toDateString(),
                'total_orders' => $invoice->total_orders,
                'billable_orders' => $invoice->billable_orders,
                'delivery_amount' => (float) $invoice->delivery_amount,
                'discount_amount' => (float) $invoice->discount_amount,
                'net_amount' => (float) $invoice->net_amount,
                'due_date' => $invoice->due_date?->toDateString(),
                'status' => $invoice->status,
                'payment_method' => $invoice->payment_method,
                'reference_number' => $invoice->reference_number,
                'paid_at' => $invoice->paid_at?->toDateTimeString(),
                'notes' => $invoice->notes,
            ],
            'orders' => $formattedOrders,
        ]);
    }

    public function pay(Request $request, ClientDeliveryInvoice $invoice, ClientDeliveryBillingService $service): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Determine Client Profile ID
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

        $statusValue = $invoice->status->value ?? $invoice->status;
        if (!in_array($statusValue, ['issued', 'overdue'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only issued or overdue invoices can be marked as paid.',
            ], 400);
        }

        $data = $request->validate([
            'payment_method'   => ['required', Rule::in(['bank_transfer', 'cash', 'cliq'])],
            'reference_number' => 'nullable|string|max:100',
        ]);

        $service->recordPayment(
            $invoice,
            $data['payment_method'],
            $data['reference_number'] ?? null,
            $user
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded. Invoice marked as paid.',
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'paid_at' => $invoice->paid_at?->toDateTimeString(),
            ]
        ]);
    }
}
