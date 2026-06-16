<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Display support center.
     */
    public function index(Request $request)
    {
        $tickets = SupportTicket::with('user', 'order')
            ->orderBy('updated_at', 'desc')
            ->get();

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
     * Send message from admin.
     */
    public function sendMessage(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id'         => Auth::id(),
            'sender_name'       => Auth::user()->name . ' (Operations)',
            'message'           => $request->input('message'),
            'is_read'           => true,
        ]);

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
        $ticket->update(['status' => 'resolved']);
        $ticket->touch();

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
