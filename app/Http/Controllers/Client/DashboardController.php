<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use App\Services\ClientDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(ClientDashboardService $dashboardService): View
    {
        $profile = $this->getClientProfile();

        $activeOrders = Order::where('client_profile_id', $profile->id)
            ->whereIn('status', ['pending', 'picked_up'])
            ->with(['receiver.city', 'receiver.area', 'payment'])
            ->latest()
            ->take(20)
            ->get();

        $metrics = $dashboardService->metrics($profile->id);

        $pendingCash = $metrics['pending_cash'];
        $balance = $metrics['balance'];
        $creditLimit = (float) ($profile->credit_limit ?? 0);
        $stats = $metrics['stats'];
        $daysTrend = $metrics['days_trend'];

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
