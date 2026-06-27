<?php

namespace App\Http\Controllers\Client;

use App\Events\SupportMessageSent;
use App\Events\SupportTicketCreated;
use App\Models\Order;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\SupportNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $this->getClientProfile();
        $userId  = Auth::id();

        $tickets = SupportTicket::where('user_id', $userId)
            ->with(['messages' => fn ($q) => $q->latest()->take(1)])
            ->latest()
            ->get();

        $activeTicket = null;
        if ($request->filled('ticket')) {
            $activeTicket = SupportTicket::where('user_id', $userId)
                ->where('id', $request->ticket)
                ->with('messages.sender')
                ->first();

            if ($activeTicket) {
                SupportMessage::where('support_ticket_id', $activeTicket->id)
                    ->whereNull('sender_id')
                    ->orWhere(function ($q) use ($userId) {
                        $q->where('support_ticket_id', '!=', null)
                          ->where('sender_id', '!=', $userId);
                    })
                    ->update(['is_read' => true]);
            }
        }

        $orders = Order::where('client_profile_id', $profile->id)
            ->with('receiver:order_id,receiver_name,receiver_phone')
            ->orderByDesc('created_at')
            ->get(['id', 'order_number', 'status']);

        return view('client.support.index', compact('tickets', 'activeTicket', 'profile', 'orders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'message'  => ['required', 'string', 'max:2000'],
            'order_id' => ['nullable', 'exists:orders,id'],
        ]);

        $ticket = SupportTicket::create([
            'user_id'  => Auth::id(),
            'order_id' => $request->order_id,
            'title'    => $request->title,
            'status'   => 'open',
            'priority' => 'medium',
        ]);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id'         => Auth::id(),
            'sender_name'       => Auth::user()->name,
            'message'           => $request->message,
            'is_read'           => false,
        ]);

        try {
            event(new SupportTicketCreated($ticket));
        } catch (\Throwable) {}

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsNewTicket($ticket));

        return redirect()->route('client.support.index', ['ticket' => $ticket->id])
            ->with('success', 'Support ticket created.');
    }

    public function show(SupportTicket $ticket): View
    {
        abort_if($ticket->user_id !== Auth::id(), 403);

        $ticket->load('messages.sender');

        SupportMessage::where('support_ticket_id', $ticket->id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['is_read' => true]);

        return view('client.support.show', compact('ticket'));
    }

    public function sendMessage(Request $request, SupportTicket $ticket): RedirectResponse|JsonResponse
    {
        abort_if($ticket->user_id !== Auth::id(), 403);

        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id'         => Auth::id(),
            'sender_name'       => Auth::user()->name,
            'message'           => $request->message,
            'is_read'           => false,
        ]);

        try {
            event(new SupportMessageSent($message));
        } catch (\Throwable) {}

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsDriverReply($ticket));

        if ($request->expectsJson()) {
            return response()->json([
                'id'          => $message->id,
                'message'     => $message->message,
                'sender_name' => $message->sender_name,
                'created_at'  => $message->created_at->format('H:i'),
            ]);
        }

        return back();
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        abort_if($ticket->user_id !== Auth::id(), 403);
        abort_if($ticket->status === 'resolved', 422);

        $ticket->update(['status' => 'resolved']);

        return redirect()->route('client.support.index', ['ticket' => $ticket->id]);
    }

    public function getMessages(Request $request, SupportTicket $ticket): JsonResponse
    {
        abort_if($ticket->user_id !== Auth::id(), 403);

        $messages = $ticket->messages()
            ->when($request->filled('after'), fn ($q) => $q->where('id', '>', $request->after))
            ->get()
            ->map(fn ($m) => [
                'id'          => $m->id,
                'message'     => $m->message,
                'sender_id'   => $m->sender_id,
                'sender_name' => $m->sender_name,
                'is_mine'     => $m->sender_id === Auth::id(),
                'created_at'  => $m->created_at->format('H:i'),
            ]);

        return response()->json($messages);
    }
}
