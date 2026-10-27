<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialLedgerEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->isDriver()) {
            return response()->json([
                'success' => false,
                'message' => __('Access denied. Only drivers can access this resource.'),
            ], 403);
        }

        // Default to today when no date range is given, so drivers see today's activity by default.
        $from = $request->filled('from') ? $request->input('from') : now()->toDateString();
        $to   = $request->filled('to') ? $request->input('to') : now()->toDateString();

        $summary = $this->buildSummary($user->id, $from, $to);

        $query = FinancialLedgerEntry::with('order')
            ->where('driver_id', $user->id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('order_number')) {
            $search = $request->input('order_number');
            $query->whereHas('order', fn ($q) => $q->where('order_number', 'like', "%{$search}%"));
        }

        // Group entries by order_id + from_account + to_account so an order's COD + delivery
        // fees (same direction) appear as one record, without merging distinct money movements
        // (e.g. collected from customer vs settled to company) into a single amount.
        // Entries with no order_id (e.g. driver_settlement) are kept as individual records.
        $merged = $query->get()
            ->groupBy(fn ($e) => $e->order_id !== null
                ? $e->order_id.'_'.$e->from_account.'_'.$e->to_account
                : 'solo_'.$e->id)
            ->map(fn ($group) => $this->mergeEntries($group))
            ->sortByDesc('created_at')
            ->values();

        $perPage = 20;
        $page    = (int) $request->input('page', 1);
        $total   = $merged->count();
        $items   = $merged->forPage($page, $perPage)->values();

        return response()->json([
            'success' => true,
            'message' => __('Finances retrieved successfully.'),
            'summary' => $summary,
            'data'    => $items,
            'meta'    => [
                'current_page' => $page,
                'last_page'    => (int) ceil($total / $perPage) ?: 1,
                'per_page'     => $perPage,
                'total'        => $total,
            ],
        ]);
    }

    private function mergeEntries($group): array
    {
        $first  = $group->first();
        $types  = $group->pluck('type')->unique()->values()->toArray();
        $latest = $group->sortByDesc('created_at')->first();

        return [
            'id'               => $first->id,
            'type'             => count($types) === 1 ? $types[0] : $types,
            'from_account'     => $first->from_account, // all rows in group share this direction
            'to_account'       => $first->to_account,   // all rows in group share this direction
            'amount'           => (float) $group->sum('amount'),
            'reference_number' => $first->reference_number,
            'notes'            => $first->notes,
            'created_at'       => $latest->created_at->toDateTimeString(),
            'order'            => $first->order ? [
                'id'              => $first->order->id,
                'order_number'    => $first->order->order_number,
                'status'          => $first->order->status,
                'payment_type'    => $first->order->payment_type,
                'payment_status'  => $first->order->payment_status,
                'order_price'     => (float) $first->order->order_price,
                'delivery_amount' => (float) $first->order->delivery_amount,
            ] : null,
        ];
    }

    /**
     * Collected / settled / pending totals, each with the orders that make up the figure.
     * Every collection and settlement ledger entry is tied to an order, so totals are built per order.
     */
    private function buildSummary(int $driverId, string $from, string $to): array
    {
        $rows = FinancialLedgerEntry::with('order.receiver')
            ->where('driver_id', $driverId)
            ->whereIn('type', ['cod_collection', 'delivery_collection', 'driver_settlement'])
            ->whereNotNull('order_id')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->get()
            ->groupBy('order_id')
            ->map(function ($entries) {
                $order     = $entries->first()->order;
                $collected = (float) $entries->where('type', '!=', 'driver_settlement')->sum('amount');
                $settled   = (float) $entries->where('type', 'driver_settlement')->sum('amount');

                return [
                    'id'             => $order?->id,
                    'order_number'   => $order?->order_number,
                    'status'         => $order?->status,
                    'payment_status' => $order?->payment_status,
                    'receiver_name'  => $order?->receiver?->receiver_name,
                    'collected'      => $collected,
                    'settled'        => $settled,
                    'pending'        => round(max(0, $collected - $settled), 2),
                    'last_activity'  => $entries->max('created_at')?->toDateTimeString(),
                ];
            })
            ->sortByDesc('last_activity')
            ->values();

        $collectedOrders = $rows->filter(fn ($row) => $row['collected'] > 0)->values();
        $settledOrders   = $rows->filter(fn ($row) => $row['settled'] > 0)->values();
        $pendingOrders   = $rows->filter(fn ($row) => $row['pending'] > 0)->values();

        return [
            'total_collected' => [
                'total'  => (float) $collectedOrders->sum('collected'),
                'orders' => $collectedOrders,
            ],
            'total_settled'   => [
                'total'  => (float) $settledOrders->sum('settled'),
                'orders' => $settledOrders,
            ],
            'pending_cash'    => [
                'total'  => round((float) $pendingOrders->sum('pending'), 2),
                'orders' => $pendingOrders,
            ],
        ];
    }
}
