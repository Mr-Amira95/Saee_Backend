<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $profile = $this->resolveClientProfile($user);

        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Client profile not found.',
                'code'    => 'CLIENT_PROFILE_NOT_FOUND',
            ], 403);
        }

        $request->validate([
            'from'   => ['nullable', 'date'],
            'to'     => ['nullable', 'date', 'after_or_equal:from'],
            'period' => ['nullable', Rule::in(['7d', '30d', '90d', 'this_month'])],
        ]);

        [$from, $to] = $this->resolveDateRange($request);

        // Base query — all columns qualified to stay safe across JOINs
        $base = Order::where('orders.client_profile_id', $profile->id)
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to);

        // ── Status counts ─────────────────────────────────────────────────────
        $statusCounts = (clone $base)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total     = array_sum($statusCounts);
        $delivered = $statusCounts['delivered']  ?? 0;
        $returned  = ($statusCounts['returned']  ?? 0) + ($statusCounts['rejected'] ?? 0);
        $pending   = $statusCounts['pending']    ?? 0;
        $inTransit = $statusCounts['picked_up']  ?? 0;
        $completed = $delivered + $returned;
        $successRate = $completed > 0 ? round(($delivered / $completed) * 100, 1) : 0.0;

        // ── Financial totals ──────────────────────────────────────────────────
        $financials = (clone $base)
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->select(
                DB::raw('SUM(order_payments.order_amount) as total_cod'),
                DB::raw('SUM(order_payments.customer_delivery_amount) as total_delivery')
            )
            ->first();

        $totalCod      = (float) ($financials->total_cod      ?? 0);
        $totalDelivery = (float) ($financials->total_delivery ?? 0);

        // ── Daily trend (zero-filled) ─────────────────────────────────────────
        $rawTrend = (clone $base)
            ->select(DB::raw('date(orders.created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $days = (int) min(
            ceil((strtotime($to) - strtotime($from)) / 86400) + 1,
            90
        );

        $dailyTrend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date         = now()->parse($to)->subDays($i)->toDateString();
            $dailyTrend[] = ['date' => $date, 'count' => (int) ($rawTrend[$date] ?? 0)];
        }

        // ── City breakdown ────────────────────────────────────────────────────
        $cityBreakdown = (clone $base)
            ->join('order_receivers', 'orders.id', '=', 'order_receivers.order_id')
            ->join('cities', 'order_receivers.city_id', '=', 'cities.id')
            ->select(
                'cities.id as city_id',
                'cities.name as city_name',
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN orders.status = "delivered"                        THEN 1 ELSE 0 END) as delivered'),
                DB::raw('SUM(CASE WHEN orders.status IN ("returned","rejected")           THEN 1 ELSE 0 END) as returned'),
                DB::raw('SUM(CASE WHEN orders.status IN ("pending","picked_up")           THEN 1 ELSE 0 END) as active')
            )
            ->groupBy('cities.id', 'cities.name')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $done        = $row->delivered + $row->returned;
                $successRate = $done > 0 ? round(($row->delivered / $done) * 100, 1) : 0.0;

                return [
                    'city_id'      => $row->city_id,
                    'city_name'    => $row->city_name,
                    'total'        => (int) $row->total,
                    'delivered'    => (int) $row->delivered,
                    'returned'     => (int) $row->returned,
                    'active'       => (int) $row->active,
                    'success_rate' => $successRate,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'message' => 'Report retrieved successfully.',
            'filters' => [
                'from'   => $from,
                'to'     => $to,
                'period' => $request->input('period'),
            ],
            'summary' => [
                'total'          => $total,
                'delivered'      => $delivered,
                'returned'       => $returned,
                'pending'        => $pending,
                'in_transit'     => $inTransit,
                'success_rate'   => $successRate,
                'total_cod'      => $totalCod,
                'total_delivery' => $totalDelivery,
                'net_cod'        => round($totalCod - $totalDelivery, 2),
            ],
            'status_breakdown' => $statusCounts,
            'daily_trend'      => $dailyTrend,
            'city_breakdown'   => $cityBreakdown,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveDateRange(Request $request): array
    {
        // Explicit dates take precedence over period shortcut
        if ($request->filled('from') || $request->filled('to')) {
            $from = $request->input('from', now()->subDays(29)->toDateString());
            $to   = $request->input('to',   now()->toDateString());
            return [$from, $to];
        }

        return match ($request->input('period', '30d')) {
            '7d'         => [now()->subDays(6)->toDateString(),          now()->toDateString()],
            '90d'        => [now()->subDays(89)->toDateString(),         now()->toDateString()],
            'this_month' => [now()->startOfMonth()->toDateString(),      now()->toDateString()],
            default      => [now()->subDays(29)->toDateString(),         now()->toDateString()],
        };
    }

    private function resolveClientProfile(User $user): ?ClientProfile
    {
        if ($user->isClientMaster()) {
            return $user->clientProfile;
        }

        if ($user->isClientEmployee()) {
            return $user->clientEmployee?->clientProfile;
        }

        return null;
    }
}
