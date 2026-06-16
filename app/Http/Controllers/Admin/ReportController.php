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
        $totalOrders = Order::count();
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 7-day orders trend for SVG chart
        $dailyTrend = Order::select(DB::raw("date(created_at) as date"), DB::raw("count(*) as count"))
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.reports.index', compact('totalOrders', 'statusCounts', 'dailyTrend'));
    }

    /**
     * KPI performance indicators dashboard.
     */
    public function kpis()
    {
        $totalOrders = Order::count();
        $completedOrders = Order::whereIn('status', ['delivered', 'rejected', 'returned'])->count();

        // return rate = (rejected + returned) / completed
        $failedCount = Order::whereIn('status', ['rejected', 'returned'])->count();
        $returnRate = $completedOrders > 0 ? round(($failedCount / $completedOrders) * 100, 1) : 0.0;

        // success rate = delivered / completed
        $deliveredCount = Order::where('status', 'delivered')->count();
        $successRate = $completedOrders > 0 ? round(($deliveredCount / $completedOrders) * 100, 1) : 100.0;

        // Average customer satisfaction
        $avgSatisfaction = round(DriverRating::avg('rating') ?? 5.0, 1);
        
        // Stars distribution
        $starsBreakdown = DriverRating::select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Drivers leaderboards (with performance getters)
        $drivers = User::where('role', 'driver')
            ->where('status', 'active')
            ->get()
            ->map(function ($driver) {
                return [
                    'driver' => $driver,
                    'rating' => $driver->average_rating,
                    'success_rate' => $driver->delivery_success_rate,
                    'transit_hours' => $driver->average_transit_hours ?? 'N/A'
                ];
            })->sortByDesc('success_rate');

        return view('admin.reports.kpis', compact('returnRate', 'successRate', 'avgSatisfaction', 'starsBreakdown', 'drivers'));
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
                fputcsv($file, ['Order ID', 'Order Number', 'Client Company', 'Driver Name', 'Receiver Name', 'Phone', 'City', 'Area', 'Payment Type', 'Goods Price (JD)', 'Delivery Price (JD)', 'Status', 'Payment Status', 'Created At']);
                
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
