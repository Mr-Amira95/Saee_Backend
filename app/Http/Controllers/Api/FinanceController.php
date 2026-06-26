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
                'message' => 'Access denied. Only drivers can access this resource.',
            ], 403);
        }

        $summary = $this->buildSummary($user->id);

        $query = FinancialLedgerEntry::with('order')
            ->where('driver_id', $user->id)
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        if ($request->filled('order_number')) {
            $search = $request->input('order_number');
            $query->whereHas('order', fn ($q) => $q->where('order_number', 'like', "%{$search}%"));
        }

        // Group entries by order_id so an order's COD + delivery fees appear as one record.
        // Entries with no order_id (e.g. driver_settlement) are kept as individual records.
        $merged = $query->get()
            ->groupBy(fn ($e) => $e->order_id !== null ? (string) $e->order_id : 'solo_'.$e->id)
            ->map(fn ($group) => $this->mergeEntries($group))
            ->sortByDesc('created_at')
            ->values();

        $perPage = 20;
        $page    = (int) $request->input('page', 1);
        $total   = $merged->count();
        $items   = $merged->forPage($page, $perPage)->values();

        return response()->json([
            'success' => true,
            'message' => 'Finances retrieved successfully.',
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
            'from_account'     => $first->from_account,
            'to_account'       => $first->to_account,
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

    private function buildSummary(int $driverId): array
    {
        $totalCollected = FinancialLedgerEntry::where('driver_id', $driverId)
            ->whereIn('type', ['cod_collection', 'delivery_collection'])
            ->sum('amount');

        $totalSettled = FinancialLedgerEntry::where('driver_id', $driverId)
            ->where('type', 'driver_settlement')
            ->sum('amount');

        return [
            'total_collected' => (float) $totalCollected,
            'total_settled'   => (float) $totalSettled,
            'pending_cash'    => (float) max(0, $totalCollected - $totalSettled),
        ];
    }
}
