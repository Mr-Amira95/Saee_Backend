<?php

namespace App\Http\Controllers\Admin;

use App\Events\SupportMessageSent;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\Order;
use App\Services\SupportNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Display support center.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with('user', 'order')
            ->withCount(['messages as unread_messages_count' => fn($q) => $q
                ->where('is_read', false)
                ->where(function($query) {
                    $query->whereNull('sender_id')
                          ->orWhereHas('sender', fn($sq) => $sq->whereNotIn('role', ['admin', 'superadmin']));
                })
            ]);

        if ($request->filled('client_id')) {
            $clientId = $request->input('client_id');
            $client = \App\Models\ClientProfile::find($clientId);
            if ($client) {
                $userIds = [$client->master_user_id];
                $employeeUserIds = $client->employees()->pluck('user_id')->toArray();
                $userIds = array_merge($userIds, $employeeUserIds);
                $query->whereIn('user_id', $userIds);
            }
        }

        $tickets = $query->orderBy('updated_at', 'desc')->get();

        $activeTicket = null;
        if ($request->filled('ticket')) {
            $activeTicket = SupportTicket::with('messages.sender', 'user', 'order')
                ->where('ticket_number', $request->input('ticket'))
                ->first();

            if ($activeTicket) {
                // Mark messages from others as read
                $activeTicket->messages()
                    ->where('sender_id', '!=', Auth::id())
                    ->update(['is_read' => true]);
            }
        }

        return view('admin.support.index', compact('tickets', 'activeTicket'));
    }

    /**
     * Return the current unread support-message count for the admin sidebar badge.
     */
    public function unreadCount()
    {
        return response()->json(['count' => SupportMessage::unreadForAdminCount()]);
    }

    /**
     * Show create ticket form.
     */
    public function create()
    {
        $users = User::whereIn('role', ['driver', 'client_master', 'client_employee'])
            ->orderBy('name')
            ->get();

        $orders = Order::orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        return view('admin.support.create', compact('users', 'orders'));
    }

    /**
     * Open a new support ticket with a client or driver.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->hasAdminAction('support.open_ticket'), 403);

        $validated = $request->validate([
            'user_id'  => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'title'    => 'required|string|max:255',
            'message'  => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'user_id'  => $validated['user_id'],
            'order_id' => $validated['order_id'] ?? null,
            'title'    => $validated['title'],
            'status'   => 'open',
        ]);

        // Create the initial message from the admin
        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id'         => Auth::id(),
            'sender_name'       => Auth::user()->name . ' (Operations)',
            'message'           => $validated['message'],
            'is_read'           => false,
        ]);

        broadcast(new SupportMessageSent($message));
        rescue(fn () => app(SupportNotificationService::class)->notifyAdminReply($ticket, Auth::id()));

        return redirect()->route('admin.support.index', ['ticket' => $ticket->ticket_number])
            ->with('success', "Support ticket {$ticket->ticket_number} opened successfully.");
    }

    /**
     * Send message from admin.
     */
    public function sendMessage(Request $request, SupportTicket $ticket)
    {
        abort_unless($request->user()->hasAdminAction('support.reply'), 403);

        $request->validate([
            'message' => 'required|string',
        ]);

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id'         => Auth::id(),
            'sender_name'       => Auth::user()->name . ' (Operations)',
            'message'           => $request->input('message'),
            'is_read'           => false,
        ]);

        broadcast(new SupportMessageSent($message));
        rescue(fn () => app(SupportNotificationService::class)->notifyAdminReply($ticket, Auth::id()));

        // Touch the ticket to update its timestamp
        $ticket->touch();
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Resolve a support ticket.
     */
    public function resolveTicket(SupportTicket $ticket)
    {
        abort_unless(auth()->user()->hasAdminAction('support.resolve'), 403);

        $ticket->update(['status' => 'resolved']);
        $ticket->touch();

        rescue(fn () => app(SupportNotificationService::class)->notifyClientTicketResolved($ticket, Auth::id()));

        return redirect()->route('admin.support.index', ['ticket' => $ticket->ticket_number])
            ->with('success', "Ticket {$ticket->ticket_number} marked as resolved.");
    }

    /**
     * Poll messaging logs for active ticket (AJAX).
     */
    public function getMessages(SupportTicket $ticket)
    {
        // Mark others' messages as read when loading them
        $ticket->messages()
            ->where('sender_id', '!=', Auth::id())
            ->update(['is_read' => true]);

        $messages = $ticket->messages()->with('sender')->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'status'   => $ticket->status
        ]);
    }
}
