<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicSupportController extends Controller
{
    /**
     * Show ticket portal.
     */
    public function show(string $token)
    {
        $ticket = SupportTicket::with('messages.sender', 'order')->where('token', $token)->firstOrFail();
        
        return view('public.support_chat', compact('ticket'));
    }

    /**
     * Store message from public user.
     */
    public function store(Request $request, string $token)
    {
        $ticket = SupportTicket::where('token', $token)->firstOrFail();

        $request->validate([
            'message' => 'required|string',
        ]);

        $senderName = 'Customer / Recipient';
        if (Auth::check()) {
            $senderName = Auth::user()->name;
            if (Auth::user()->isDriver()) {
                $senderName .= ' (Driver)';
            } elseif (Auth::user()->isClientMaster() || Auth::user()->isClientEmployee()) {
                $senderName .= ' (Client)';
            }
        } elseif ($ticket->user) {
            $senderName = $ticket->user->name;
            if ($ticket->user->isDriver()) {
                $senderName .= ' (Driver)';
            } elseif ($ticket->user->isClientMaster() || $ticket->user->isClientEmployee()) {
                $senderName .= ' (Client)';
            }
        }

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id'         => Auth::id(), // Null if guest
            'sender_name'       => $senderName,
            'message'           => $request->input('message'),
            'is_read'           => false,
        ]);

        $ticket->touch();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Poll messages (AJAX) for guest.
     */
    public function getMessages(string $token)
    {
        $ticket = SupportTicket::where('token', $token)->firstOrFail();
        $messages = $ticket->messages()->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'status'   => $ticket->status
        ]);
    }
}
