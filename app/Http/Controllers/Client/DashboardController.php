<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $profile = $this->getClientProfile();

        $activeOrders = Order::where('client_profile_id', $profile->id)
            ->whereIn('status', ['pending', 'picked_up'])
            ->with(['receiver.city', 'receiver.area'])
            ->latest()
            ->take(20)
            ->get();

        $balance = (float) ($profile->balance ?? 0);
        $creditLimit = (float) ($profile->credit_limit ?? 0);

        $stats = [
            'pending' => Order::where('client_profile_id', $profile->id)->where('status', 'pending')->count(),
            'picked_up' => Order::where('client_profile_id', $profile->id)->where('status', 'picked_up')->count(),
            'delivered_today' => Order::where('client_profile_id', $profile->id)->where('status', 'delivered')->whereDate('created_at', now()->toDateString())->count(),
            'returned' => Order::where('client_profile_id', $profile->id)->whereIn('status', ['returned', 'rejected'])->count(),
        ];

        // 14-day orders trend for SVG chart
        $dailyTrend = Order::where('client_profile_id', $profile->id)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->select(DB::raw("date(created_at) as date"), DB::raw("count(*) as count"))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $daysTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $daysTrend[$date] = $dailyTrend[$date] ?? 0;
        }

        return view('client.dashboard.index', compact('activeOrders', 'profile', 'balance', 'creditLimit', 'stats', 'daysTrend'));
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
                      ->orWhere('receiver_name', 'like', "%{$query}%")
                      ->orWhere('receiver_phone', 'like', "%{$query}%");
                })
                ->with(['city', 'area', 'trackingLogs'])
                ->latest()
                ->take(10)
                ->get();
        }

        return view('client.dashboard.track', compact('orders', 'query', 'profile'));
    }
}
