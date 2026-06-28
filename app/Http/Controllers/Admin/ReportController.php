<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Attendance;
use App\Models\DriverRating;
use App\Models\FinancialLedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Operational dashboard & reports center.
     */
    public function index()
    {
        $totalOrders  = Order::count();
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 7-day trend, zero-filled so every day always has an entry
        $rawTrend = Order::select(DB::raw("date(created_at) as date"), DB::raw("count(*) as count"))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $dailyTrend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dailyTrend->push((object) ['date' => $date, 'count' => $rawTrend[$date] ?? 0]);
        }

        $activeDrivers = User::where('role', 'driver')->where('status', 'active')->count();
        $activeClients = User::where('role', 'client_master')->where('status', 'active')->count();

        return view('admin.reports.index', compact(
            'totalOrders', 'statusCounts', 'dailyTrend', 'activeDrivers', 'activeClients'
        ));
    }

    /**
     * KPI performance indicators dashboard.
     */
    public function kpis()
    {
        $totalOrders     = Order::count();
        $completedOrders = Order::whereIn('status', ['delivered', 'rejected', 'returned'])->count();

        $failedCount    = Order::whereIn('status', ['rejected', 'returned'])->count();
        $deliveredCount = Order::where('status', 'delivered')->count();
        $returnRate     = $completedOrders > 0 ? round(($failedCount    / $completedOrders) * 100, 1) : 0.0;
        $successRate    = $completedOrders > 0 ? round(($deliveredCount / $completedOrders) * 100, 1) : 100.0;

        $avgSatisfaction = round(DriverRating::avg('rating') ?? 5.0, 1);
        $totalRatings    = DriverRating::count();

        $starsBreakdown = DriverRating::select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $drivers = User::where('role', 'driver')
            ->where('status', 'active')
            ->with('driverProfile')
            ->get()
            ->map(function ($driver) {
                $profileId    = $driver->driverProfile?->id ?? 0;
                $statusCounts = Order::where('driver_profile_id', $profileId)
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

                $delivered   = $statusCounts['delivered'] ?? 0;
                $failed      = ($statusCounts['rejected'] ?? 0) + ($statusCounts['returned'] ?? 0);
                $completed   = $delivered + $failed;
                $successRate = $completed > 0 ? round(($delivered / $completed) * 100, 1) : 100.0;

                return [
                    'driver'          => $driver,
                    'rating'          => $driver->average_rating,
                    'success_rate'    => $successRate,
                    'delivered_count' => $delivered,
                    'failed_count'    => $failed,
                ];
            })
            ->sortByDesc('success_rate')
            ->values();

        return view('admin.reports.kpis', compact(
            'returnRate', 'successRate', 'avgSatisfaction',
            'starsBreakdown', 'drivers', 'totalRatings',
            'totalOrders', 'deliveredCount', 'failedCount'
        ));
    }

    /**
     * Per-driver KPI breakdown page.
     */
    public function driverKpi(User $driver)
    {
        abort_if($driver->role !== 'driver', 404);

        $profile   = $driver->driverProfile;
        $profileId = $profile?->id ?? 0;

        $statusCounts = Order::where('driver_profile_id', $profileId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $delivered   = $statusCounts['delivered']                                     ?? 0;
        $failed      = ($statusCounts['rejected'] ?? 0) + ($statusCounts['returned'] ?? 0);
        $inTransit   = ($statusCounts['picked_up'] ?? 0) + ($statusCounts['pending'] ?? 0);
        $totalOrders = array_sum($statusCounts);
        $completed   = $delivered + $failed;
        $successRate = $completed > 0 ? round(($delivered / $completed) * 100, 1) : 100.0;
        $avgRating   = round(DriverRating::where('driver_id', $driver->id)->avg('rating') ?? 0, 1);
        $totalRatings = DriverRating::where('driver_id', $driver->id)->count();

        // 30-day order trend, zero-filled
        $rawTrend = Order::where('driver_profile_id', $profileId)
            ->select(DB::raw("date(created_at) as date"), DB::raw("count(*) as count"))
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $monthlyTrend = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $monthlyTrend->push((object) ['date' => $date, 'count' => $rawTrend[$date] ?? 0]);
        }

        $recentRatings = DriverRating::with('order')
            ->where('driver_id', $driver->id)
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.reports.driver_kpi', compact(
            'driver', 'profile', 'totalOrders', 'delivered', 'failed',
            'inTransit', 'successRate', 'avgRating', 'totalRatings',
            'monthlyTrend', 'recentRatings', 'statusCounts'
        ));
    }

    /**
     * Paginated ratings list with filters.
     */
    public function ratings(Request $request)
    {
        $query = DriverRating::with(['driver', 'order'])
            ->latest('driver_ratings.created_at');

        if ($request->filled('driver')) {
            $query->where('driver_id', $request->driver);
        }
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('driver_ratings.created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('driver_ratings.created_at', '<=', $request->date_to);
        }

        $ratings = $query->paginate(25)->withQueryString();
        $drivers = User::where('role', 'driver')->orderBy('name')->get(['id', 'name']);
        $avgAll  = round(DriverRating::avg('rating') ?? 0, 1);
        $totalAll = DriverRating::count();

        return view('admin.reports.ratings', compact('ratings', 'drivers', 'avgAll', 'totalAll'));
    }

    /**
     * Standardized CSV Excel table exporter.
     */
    public function export(string $table)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"export_{$table}_" . now()->format('YmdHis') . '.csv"',
        ];

        $callback = function () use ($table) {
            $file = fopen('php://output', 'w');

            if ($table === 'orders') {
                // Header
                fputcsv($file, ['Order ID', 'Order Number', 'Client Company', 'Driver Name', 'Receiver Name', 'Phone', 'City', 'Area', 'Payment Type', 'Goods Price (JD)', 'Delivery Price (JD)', 'Status', 'Payment Status', 'Delivery Shift', 'Created At']);
                
                Order::with('clientProfile', 'driver', 'city', 'area')
                    ->chunk(200, function ($orders) use ($file) {
                        foreach ($orders as $o) {
                            fputcsv($file, [
                                $o->id,
                                $o->order_number,
                                $o->clientProfile->company_name ?? 'N/A',
                                $o->driver->name ?? 'Unassigned',
                                $o->receiver_name,
                                $o->receiver_phone,
                                $o->city->name ?? 'N/A',
                                $o->area->name ?? 'N/A',
                                strtoupper($o->payment_type),
                                $o->order_price ?? 0.00,
                                $o->delivery_amount,
                                ucfirst($o->status),
                                ucfirst($o->payment_status),
                                $o->delivery_shift?->label() ?? "Doesn't Matter",
                                $o->created_at->toDateTimeString()
                            ]);
                        }
                    });

            } elseif ($table === 'financials') {
                fputcsv($file, ['Entry ID', 'Order Number', 'Client Company', 'Driver Name', 'From Account', 'To Account', 'Amount (JD)', 'Type', 'Reference Num', 'Recorded By', 'Created At']);
                
                FinancialLedgerEntry::with('order', 'clientProfile', 'driver', 'recordedBy')
                    ->chunk(200, function ($entries) use ($file) {
                        foreach ($entries as $e) {
                            fputcsv($file, [
                                $e->id,
                                $e->order->order_number ?? 'N/A',
                                $e->clientProfile->company_name ?? 'N/A',
                                $e->driver->name ?? 'N/A',
                                ucfirst($e->from_account),
                                ucfirst($e->to_account),
                                $e->amount,
                                str_replace('_', ' ', $e->type),
                                $e->reference_number ?? 'N/A',
                                $e->recordedBy->name ?? 'System',
                                $e->created_at->toDateTimeString()
                            ]);
                        }
                    });

            } elseif ($table === 'attendance') {
                fputcsv($file, ['Attendance ID', 'User Name', 'Role', 'Date', 'Check In Time', 'Check Out Time', 'Check In GPS', 'Check Out GPS', 'Created At']);
                
                Attendance::with('user')
                    ->chunk(200, function ($attendances) use ($file) {
                        foreach ($attendances as $a) {
                            fputcsv($file, [
                                $a->id,
                                $a->user->name ?? 'N/A',
                                ucfirst($a->user->role ?? 'N/A'),
                                $a->date->toDateString(),
                                $a->check_in_at->toDateTimeString(),
                                $a->check_out_at ? $a->check_out_at->toDateTimeString() : 'Active',
                                $a->check_in_location ?? 'N/A',
                                $a->check_out_location ?? 'N/A',
                                $a->created_at->toDateTimeString()
                            ]);
                        }
                    });

            } elseif ($table === 'ratings') {
                fputcsv($file, ['Rating ID', 'Order Number', 'Driver Name', 'Rating Stars', 'Customer Review Comment', 'Created At']);
                
                DriverRating::with('order', 'driver')
                    ->chunk(200, function ($ratings) use ($file) {
                        foreach ($ratings as $r) {
                            fputcsv($file, [
                                $r->id,
                                $r->order->order_number ?? 'N/A',
                                $r->driver->name ?? 'N/A',
                                $r->rating,
                                $r->comment ?? 'No comment',
                                $r->created_at->toDateTimeString()
                            ]);
                        }
                    });
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
