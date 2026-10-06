<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Services\WaybillExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Drill-down filters for the "Operational Order Status" cards => underlying order statuses */
    private const STATUS_FILTERS = [
        'pending'         => ['pending'],
        'in_transit'      => ['picked_up'],
        'delivered'       => ['delivered'],
        'returned_failed' => ['returned', 'rejected'],
        'cancelled'       => ['cancelled'],
    ];

    public function index(Request $request): View
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

        // 3. Pending Dispatch / Action Widget
        $unassignedOrdersCount = Order::whereNull('driver_profile_id')->count();
        
        $openTickets = SupportTicket::with(['user.clientProfile', 'user.clientEmployee.clientProfile'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        $openTicketsCount = SupportTicket::whereIn('status', ['pending', 'in_progress'])->count();

        // 4. Live driver map preview (only for admins allowed to see the full live map)
        $canViewLiveMap = auth()->user()->hasAdminAction('drivers.live_map');
        $mapDrivers = $canViewLiveMap
            ? DriverProfile::with('user')
                ->whereNotNull('current_latitude')
                ->whereNotNull('current_longitude')
                ->get(['id', 'user_id', 'current_latitude', 'current_longitude', 'location_updated_at'])
            : collect();

        // 5. Drill-down order list for the clicked status card
        $selectedStatus = $this->resolveStatus($request);

        $statusOrders = null;
        if ($selectedStatus !== null) {
            $statusOrders = $this->statusOrdersQuery($selectedStatus)
                ->with(['clientProfile', 'receiver.city', 'payment', 'driverProfile.user'])
                ->paginate(20)
                ->withQueryString()
                ->fragment('order-status');
        }

        return view('admin.dashboard', compact(
            'activeDriversCount',
            'activeClientsCount',
            'totalOrdersCount',
            'totalRevenue',
            'statusCounts',
            'unassignedOrdersCount',
            'openTickets',
            'openTicketsCount',
            'canViewLiveMap',
            'mapDrivers',
            'selectedStatus',
            'statusOrders'
        ));
    }

    /**
     * Export PDF (waybills) for every order in the selected status drill-down.
     */
    public function exportPdf(Request $request, WaybillExportService $waybills)
    {
        $status = $this->resolveStatus($request);
        abort_if($status === null, 404);

        return $waybills->render($this->statusOrdersQuery($status));
    }

    private function resolveStatus(Request $request): ?string
    {
        $status = $request->query('status');

        return is_string($status) && isset(self::STATUS_FILTERS[$status]) ? $status : null;
    }

    private function statusOrdersQuery(string $status)
    {
        return Order::whereIn('status', self::STATUS_FILTERS[$status])->latest();
    }
}
