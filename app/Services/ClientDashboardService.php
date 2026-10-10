<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Client dashboard metrics shared by the web dashboard and the mobile API (/api/home).
 */
class ClientDashboardService
{
    public function metrics(int $clientProfileId): array
    {
        $amountSql = 'COALESCE(SUM(COALESCE(order_payments.order_amount, 0) + COALESCE(CASE WHEN order_payments.delivery_on_customer = 1 THEN order_payments.customer_delivery_amount ELSE 0 END, 0)), 0) as total';

        // Cash in transit & pending collection, from active orders
        $pendingCash = (float) Order::where('client_profile_id', $clientProfileId)
            ->whereIn('status', ['pending', 'picked_up'])
            ->where('payment_status', '!=', 'paid')
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->selectRaw($amountSql)
            ->value('total');

        // Cash from delivered orders awaiting payout to the client
        $balance = (float) Order::where('client_profile_id', $clientProfileId)
            ->where('status', 'delivered')
            ->where('payment_status', '!=', 'paid')
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->selectRaw($amountSql)
            ->value('total');

        $stats = [
            'pending' => Order::where('client_profile_id', $clientProfileId)->where('status', 'pending')->count(),
            'picked_up' => Order::where('client_profile_id', $clientProfileId)->whereIn('status', ['assigned', 'picked_up'])->count(),
            'delivered_today' => Order::where('client_profile_id', $clientProfileId)
                ->where('status', 'delivered')
                ->whereBetween('delivered_at', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
            'returned' => Order::where('client_profile_id', $clientProfileId)->whereIn('status', ['returned', 'rejected'])->count(),
        ];

        // 7-day orders trend (oldest -> newest, including today, zero-filled)
        $dailyTrend = Order::where('client_profile_id', $clientProfileId)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->select(DB::raw('date(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $daysTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $daysTrend[$date] = (int) ($dailyTrend[$date] ?? 0);
        }

        return [
            'pending_cash' => $pendingCash,
            'balance' => $balance,
            'stats' => $stats,
            'days_trend' => $daysTrend,
        ];
    }
}
