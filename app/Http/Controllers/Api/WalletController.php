<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialLedgerEntry;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isClientMaster() && ! $user->isClientEmployee()) {
            return response()->json([
                'success' => false,
                'message' => 'Only client accounts can access wallet information.',
            ], 403);
        }

        $clientProfile = $user->isClientMaster()
            ? $user->clientProfile
            : $user->clientEmployee?->clientProfile;

        if (! $clientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Client profile not found.',
                'code'    => 'CLIENT_PROFILE_NOT_FOUND',
            ], 403);
        }

        $orderStats = Order::where('client_profile_id', $clientProfile->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'picked_up' THEN 1 ELSE 0 END) as in_transit,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = 'returned'  THEN 1 ELSE 0 END) as returned
            ")
            ->first();

        $transactions = FinancialLedgerEntry::where('client_profile_id', $clientProfile->id)
            ->with('order:id,order_number')
            ->latest()
            ->paginate(15);

        $invoices = Invoice::where('client_profile_id', $clientProfile->id)
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Wallet retrieved successfully.',
            'data'    => [
                'balance'      => (float) $clientProfile->balance,
                'credit_limit' => (float) $clientProfile->credit_limit,
                'order_stats'  => [
                    'total'      => (int) $orderStats->total,
                    'pending'    => (int) $orderStats->pending,
                    'in_transit' => (int) $orderStats->in_transit,
                    'delivered'  => (int) $orderStats->delivered,
                    'returned'   => (int) $orderStats->returned,
                ],
                'transactions' => $transactions->map(fn ($t) => [
                    'id'           => $t->id,
                    'type'         => $t->type,
                    'amount'       => (float) $t->amount,
                    'from_account' => $t->from_account,
                    'to_account'   => $t->to_account,
                    'order_number' => $t->order?->order_number,
                    'notes'        => $t->notes,
                    'created_at'   => $t->created_at->toDateTimeString(),
                ]),
                'transactions_meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page'    => $transactions->lastPage(),
                    'per_page'     => $transactions->perPage(),
                    'total'        => $transactions->total(),
                ],
                'invoices' => $invoices->map(fn ($inv) => [
                    'id'              => $inv->id,
                    'invoice_number'  => $inv->invoice_number,
                    'total_orders'    => $inv->total_orders,
                    'cod_amount'      => (float) $inv->cod_amount,
                    'shipping_amount' => (float) $inv->shipping_amount,
                    'net_amount'      => (float) $inv->net_amount,
                    'status'          => $inv->status,
                    'created_at'      => $inv->created_at->toDateTimeString(),
                ]),
            ],
        ]);
    }
}
