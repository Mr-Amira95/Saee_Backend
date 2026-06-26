<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FinanceLedgerResource;
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

        $entries = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Finances retrieved successfully.',
            'summary' => $summary,
            'data'    => FinanceLedgerResource::collection($entries->items()),
            'meta'    => [
                'current_page' => $entries->currentPage(),
                'last_page'    => $entries->lastPage(),
                'per_page'     => $entries->perPage(),
                'total'        => $entries->total(),
            ],
        ]);
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
