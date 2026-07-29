<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\OrderTrackingLog;
use App\Models\Attendance;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 1. Metric stats
        $activeDriversCount = DriverProfile::whereHas('user', fn($q) => $q->where('status', 'active'))->count();
        $activeClientsCount = ClientProfile::where('status', 'active')->count();
        $totalOrdersCount = Order::count();
        $totalRevenue = Order::where('status', 'delivered')
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->sum('order_payments.client_delivery_amount');

        // 2. Status counts for Operational distribution
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 3. Dynamic Activity logs
        // Latest order updates
        $orderUpdates = OrderTrackingLog::with('order')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                $status = __(ucfirst(str_replace('_', ' ', $log->status)));
                return [
                    'dot_color' => '#3b82f6', // Info blue
                    'message' => __('Order :number status updated to :status', [
                        'number' => '<strong>#' . ($log->order->order_number ?? __('N/A')) . '</strong>',
                        'status' => '<strong>' . $status . '</strong>',
                    ]) . ($log->notes ? " ({$log->notes})" : ''),
                    'time' => $log->created_at,
                ];
            });

        // Latest driver check-in/outs
        $attendances = Attendance::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($att) {
                $message = $att->check_out_at
                    ? __('Driver :name checked out of their shift', ['name' => '<strong>' . ($att->user->name ?? __('N/A')) . '</strong>'])
                    : __('Driver :name checked into their shift', ['name' => '<strong>' . ($att->user->name ?? __('N/A')) . '</strong>']);
                return [
                    'dot_color' => $att->check_out_at ? '#ef4444' : '#10b981', // Red for out, green for in
                    'message' => $message,
                    'time' => $att->updated_at,
                ];
            });

        // Merge, sort desc by time, and slice to 5 items
        $recentActivities = $orderUpdates->concat($attendances)
            ->sortByDesc('time')
            ->take(5);

        // 4. Pending Dispatch / Action Widget
        $unassignedOrdersCount = Order::whereNull('driver_profile_id')->count();
        
        $openTickets = SupportTicket::with(['user.clientProfile', 'user.clientEmployee.clientProfile'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        $openTicketsCount = SupportTicket::whereIn('status', ['pending', 'in_progress'])->count();

        return view('admin.dashboard', compact(
            'activeDriversCount',
            'activeClientsCount',
            'totalOrdersCount',
            'totalRevenue',
            'statusCounts',
            'recentActivities',
            'unassignedOrdersCount',
            'openTickets',
            'openTicketsCount'
        ));
    }
}
