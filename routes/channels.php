<?php

use App\Models\SupportTicket;
use Illuminate\Support\Facades\Broadcast;

// Private channel for support ticket messages.
// Accessible to the ticket owner or any admin/superadmin.
Broadcast::channel('support.{ticketId}', function ($user, int $ticketId) {
    if (in_array($user->role, ['admin', 'superadmin'])) {
        return true;
    }

    return SupportTicket::where('id', $ticketId)
        ->where('user_id', $user->id)
        ->exists();
});
