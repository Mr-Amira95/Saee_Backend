<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $profile = $this->getClientProfile();

        $activeOrders = Order::where('client_profile_id', $profile->id)
            ->whereIn('status', ['pending', 'picked_up'])
            ->with(['receiver.city', 'receiver.area', 'payment'])
            ->latest()
            ->take(20)
            ->get();

        $pendingCash = (float) Order::where('client_profile_id', $profile->id)
            ->whereIn('status', ['pending', 'picked_up'])
            ->where('payment_status', '!=', 'paid')
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->selectRaw('COALESCE(SUM(COALESCE(order_payments.order_amount, 0) + COALESCE(CASE WHEN order_payments.delivery_on_customer = 1 THEN order_payments.customer_delivery_amount ELSE 0 END, 0)), 0) as total')
            ->value('total');

        $balance = (float) Order::where('client_profile_id', $profile->id)
            ->where('status', 'delivered')
            ->where('payment_status', '!=', 'paid')
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->selectRaw('COALESCE(SUM(COALESCE(order_payments.order_amount, 0) + COALESCE(CASE WHEN order_payments.delivery_on_customer = 1 THEN order_payments.customer_delivery_amount ELSE 0 END, 0)), 0) as total')
            ->value('total');

        $creditLimit = (float) ($profile->credit_limit ?? 0);

        $stats = [
            'pending' => Order::where('client_profile_id', $profile->id)->where('status', 'pending')->count(),
            'picked_up' => Order::where('client_profile_id', $profile->id)->whereIn('status', ['assigned', 'picked_up'])->count(),
            'delivered_today' => Order::where('client_profile_id', $profile->id)->where('status', 'delivered')->whereDate('created_at', now()->toDateString())->count(),
            'returned' => Order::where('client_profile_id', $profile->id)->whereIn('status', ['returned', 'rejected'])->count(),
        ];

        // 7-day orders trend for SVG chart
        $dailyTrend = Order::where('client_profile_id', $profile->id)
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
            $daysTrend[$date] = $dailyTrend[$date] ?? 0;
        }

        return view('client.dashboard.index', compact('activeOrders', 'profile', 'balance', 'creditLimit', 'stats', 'daysTrend', 'pendingCash'));
    }

    public function track(Request $request): View
    {
        $profile = $this->getClientProfile();
        $query = trim($request->get('q', ''));
        $orders = collect();

        if ($query) {
            $orders = Order::where('client_profile_id', $profile->id)
                ->where(function ($q) use ($query) {
                    $q->where('order_number', $query)
                        ->orWhereHas('receiver', function ($sub) use ($query) {
                            $sub->where('receiver_name', 'like', "%{$query}%")
                                ->orWhere('receiver_phone', 'like', "%{$query}%");
                        });
                })
                ->with(['receiver.city', 'receiver.area', 'trackingLogs', 'payment'])
                ->latest()
                ->take(10)
                ->get();
        }

        return view('client.dashboard.track', compact('orders', 'query', 'profile'));
    }
}
