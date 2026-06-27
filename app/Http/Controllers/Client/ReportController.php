<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $profile = $this->getClientProfile();

        // Date range defaults: last 30 days
        $from = $request->filled('from') ? $request->input('from') : now()->subDays(29)->toDateString();
        $to   = $request->filled('to')   ? $request->input('to')   : now()->toDateString();

        // Always qualify orders.created_at so JOIN queries remain unambiguous
        $base = Order::where('orders.client_profile_id', $profile->id)
            ->whereDate('orders.created_at', '>=', $from)
            ->whereDate('orders.created_at', '<=', $to);

        // Status counts
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

        // COD & delivery amounts (JOIN is now safe — all wheres are already qualified)
        $financials = (clone $base)
            ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
            ->select(
                DB::raw('SUM(order_payments.order_amount) as total_cod'),
                DB::raw('SUM(order_payments.customer_delivery_amount) as total_delivery')
            )
            ->first();

        $totalCod      = (float) ($financials->total_cod      ?? 0);
        $totalDelivery = (float) ($financials->total_delivery ?? 0);

        // Daily trend (zero-filled)
        $rawTrend = (clone $base)
            ->select(DB::raw('date(orders.created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $days = (int) min(ceil((strtotime($to) - strtotime($from)) / 86400) + 1, 90);
        $dailyTrend = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->parse($to)->subDays($i)->toDateString();
            $dailyTrend->push((object) ['date' => $date, 'count' => $rawTrend[$date] ?? 0]);
        }

        // City breakdown (city_id lives on order_receivers, not orders)
        $cityBreakdown = (clone $base)
            ->join('order_receivers', 'orders.id', '=', 'order_receivers.order_id')
            ->join('cities', 'order_receivers.city_id', '=', 'cities.id')
            ->select(
                'cities.name as city_name',
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN orders.status = "delivered" THEN 1 ELSE 0 END) as delivered'),
                DB::raw('SUM(CASE WHEN orders.status IN ("returned","rejected") THEN 1 ELSE 0 END) as returned'),
                DB::raw('SUM(CASE WHEN orders.status IN ("pending","picked_up") THEN 1 ELSE 0 END) as active')
            )
            ->groupBy('cities.id', 'cities.name')
            ->orderByDesc('total')
            ->get();

        return view('client.reports.index', compact(
            'profile', 'from', 'to',
            'total', 'delivered', 'returned', 'pending', 'inTransit',
            'successRate', 'totalCod', 'totalDelivery',
            'statusCounts', 'dailyTrend', 'cityBreakdown'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $profile = $this->getClientProfile();

        $from = $request->filled('from') ? $request->from : now()->subDays(29)->toDateString();
        $to   = $request->filled('to')   ? $request->to   : now()->toDateString();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders_report_' . $from . '_to_' . $to . '.csv"',
        ];

        $callback = function () use ($profile, $from, $to) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Order #', 'Date', 'Receiver Name', 'Phone', 'City', 'Area',
                'Payment Type', 'Order Amount (JD)', 'Delivery Fee (JD)',
                'Status', 'Payment Status', 'Delivered At', 'Returned At',
            ]);

            Order::where('client_profile_id', $profile->id)
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->with('city', 'area', 'payment')
                ->orderBy('created_at', 'desc')
                ->chunk(200, function ($orders) use ($file) {
                    foreach ($orders as $o) {
                        fputcsv($file, [
                            $o->order_number,
                            $o->created_at->toDateString(),
                            $o->receiver_name,
                            $o->receiver_phone,
                            optional($o->city)->name  ?? '',
                            optional($o->area)->name  ?? '',
                            strtoupper($o->payment_type ?? ''),
                            $o->payment?->order_amount ?? 0,
                            $o->payment?->customer_delivery_amount ?? 0,
                            ucfirst($o->status),
                            ucfirst($o->payment_status ?? ''),
                            $o->delivered_at?->toDateString() ?? '',
                            $o->returned_at?->toDateString()  ?? '',
                        ]);
                    }
                });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
