<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WhatsAppLogController extends Controller
{
    /**
     * List WhatsApp activity grouped into one conversation per phone number,
     * merging messages across every order that phone has been attached to.
     */
    public function index(Request $request)
    {
        $logs = WhatsAppLog::with('order:id,order_number,status,created_at')
            ->orderBy('created_at')
            ->get(['id', 'order_id', 'phone', 'message', 'status', 'direction', 'message_type', 'created_at']);

        $conversations = $this->groupByPhone($logs);

        if ($request->filled('search')) {
            $search = mb_strtolower($request->input('search'));
            $searchDigits = preg_replace('/\D/', '', $search);

            $conversations = $conversations->filter(function ($c) use ($search, $searchDigits) {
                if ($searchDigits !== '' && str_contains($c->phone, $searchDigits)) {
                    return true;
                }

                if ($c->customerName && str_contains(mb_strtolower($c->customerName), $search)) {
                    return true;
                }

                return $c->orders->contains(fn ($o) => str_contains(mb_strtolower($o->order_number), $search));
            });
        }

        if ($request->filled('type')) {
            if ($request->input('type') === 'replied') {
                $conversations = $conversations->filter(fn ($c) => $c->inboundCount > 0);
            } elseif ($request->input('type') === 'no_reply') {
                $conversations = $conversations->filter(fn ($c) => $c->inboundCount === 0);
            }
        }

        $conversations = $conversations->sortByDesc(fn ($c) => $c->lastActivity)->values();

        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginated = new LengthAwarePaginator(
            $conversations->forPage($page, $perPage)->values(),
            $conversations->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'conversations'  => $conversations->count(),
            'messages'       => $logs->count(),
            'inbound_today'  => $logs->where('direction', 'inbound')->filter(fn ($l) => $l->created_at->isToday())->count(),
            'awaiting_reply' => $conversations->where('inboundCount', 0)->count(),
        ];

        return view('admin.whatsapp-logs.index', ['conversations' => $paginated, 'stats' => $stats]);
    }

    /**
     * Show one unified chat thread for a phone number, across every order
     * that phone has messaged about.
     */
    public function show(string $phone)
    {
        $normalized = WhatsAppLog::normalizePhone($phone);

        $logs = WhatsAppLog::with(['order.receiver.city', 'order.receiver.area', 'order.driverProfile.user'])
            ->get()
            ->filter(fn (WhatsAppLog $log) => WhatsAppLog::normalizePhone($log->phone) === $normalized)
            ->sortBy('created_at')
            ->values();

        abort_if($logs->isEmpty(), 404);

        $orders = $logs->pluck('order')->filter()->unique('id')->sortByDesc('created_at')->values();
        $latestOrder = $orders->first();
        $latestReceiver = $orders->first(fn ($o) => $o->receiver)?->receiver;

        return view('admin.whatsapp-logs.show', [
            'phone'          => $normalized,
            'displayPhone'   => $latestReceiver?->receiver_phone ?? $logs->last()->phone,
            'customerName'   => $latestReceiver?->receiver_name,
            'logs'           => $logs,
            'orders'         => $orders,
            'latestOrder'    => $latestOrder,
            'latestReceiver' => $latestReceiver,
        ]);
    }

    /**
     * Roll a flat log collection up into one summary object per phone number.
     */
    private function groupByPhone(Collection $logs): Collection
    {
        return $logs
            ->groupBy(fn (WhatsAppLog $log) => WhatsAppLog::normalizePhone($log->phone))
            ->map(function (Collection $group, string $normalizedPhone) {
                $orders = $group->pluck('order')->filter()->unique('id')->sortByDesc('created_at')->values();
                $latestReceiver = $orders->first(fn ($o) => $o->receiver)?->receiver;
                $lastLog = $group->last();

                return (object) [
                    'phone'         => $normalizedPhone,
                    'displayPhone'  => $latestReceiver?->receiver_phone ?? $lastLog->phone,
                    'customerName'  => $latestReceiver?->receiver_name,
                    'orders'        => $orders,
                    'messageCount'  => $group->count(),
                    'inboundCount'  => $group->where('direction', 'inbound')->count(),
                    'lastLog'       => $lastLog,
                    'lastActivity'  => $lastLog->created_at,
                ];
            })
            ->values();
    }
}
