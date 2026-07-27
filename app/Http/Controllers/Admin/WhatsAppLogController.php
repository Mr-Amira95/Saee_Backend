<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WhatsAppLog;
use Illuminate\Http\Request;

class WhatsAppLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::whereHas('whatsappLogs')
            ->with('receiver')
            ->withCount('whatsappLogs')
            ->withCount(['whatsappLogs as inbound_logs_count' => fn ($q) => $q->where('direction', 'inbound')])
            ->withMax('whatsappLogs', 'created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('receiver', fn ($rq) => $rq
                      ->where('receiver_name', 'like', "%{$search}%")
                      ->orWhere('receiver_phone', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('type')) {
            if ($request->input('type') === 'replied') {
                $query->has('whatsappLogs', '>=', 1)
                    ->whereHas('whatsappLogs', fn ($q) => $q->where('direction', 'inbound'));
            } elseif ($request->input('type') === 'no_reply') {
                $query->whereDoesntHave('whatsappLogs', fn ($q) => $q->where('direction', 'inbound'));
            }
        }

        $orders = $query->orderByDesc('whatsapp_logs_max_created_at')->paginate(25)->withQueryString();

        $stats = [
            'conversations' => Order::whereHas('whatsappLogs')->count(),
            'messages'      => WhatsAppLog::count(),
            'inbound_today' => WhatsAppLog::where('direction', 'inbound')->whereDate('created_at', today())->count(),
            'awaiting_reply' => Order::whereHas('whatsappLogs')
                ->whereDoesntHave('whatsappLogs', fn ($q) => $q->where('direction', 'inbound'))
                ->count(),
        ];

        return view('admin.whatsapp-logs.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['whatsappLogs', 'receiver.city', 'receiver.area', 'driverProfile.user']);

        return view('admin.whatsapp-logs.show', compact('order'));
    }
}
