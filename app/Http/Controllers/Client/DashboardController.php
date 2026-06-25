<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $profile = $this->getClientProfile();

        $activeOrders = Order::where('client_profile_id', $profile->id)
            ->whereIn('status', ['pending', 'picked_up'])
            ->with(['city', 'area'])
            ->latest()
            ->take(20)
            ->get();

        return view('client.dashboard.index', compact('activeOrders', 'profile'));
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
