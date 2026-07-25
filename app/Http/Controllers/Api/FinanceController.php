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

    private function buildSummary(int $driverId, string $from, string $to): array
    {
        $totalCollected = FinancialLedgerEntry::where('driver_id', $driverId)
            ->whereIn('type', ['cod_collection', 'delivery_collection'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('amount');

        $totalSettled = FinancialLedgerEntry::where('driver_id', $driverId)
            ->where('type', 'driver_settlement')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('amount');

        return [
            'total_collected' => (float) $totalCollected,
            'total_settled'   => (float) $totalSettled,
            'pending_cash'    => (float) max(0, $totalCollected - $totalSettled),
        ];
    }
}
