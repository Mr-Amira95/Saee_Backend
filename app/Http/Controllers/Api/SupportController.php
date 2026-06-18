<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SupportMessageResource;
use App\Http\Resources\Api\SupportTicketResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $tickets = $user->supportTickets()
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->where('is_read', false)
                    ->where('sender_id', '!=', $user->id),
            ])
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => SupportTicketResource::collection($tickets->items()),
            'meta'    => [
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
                'per_page'     => $tickets->perPage(),
                'total'        => $tickets->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $ticket = $user->supportTickets()->with('messages')->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Support ticket not found',
                'code'    => 'TICKET_NOT_FOUND',
            ], 404);
        }

        // Mark support/admin messages as read now that the user is viewing them
        $ticket->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data'    => new SupportTicketResource($ticket),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|in:general,delivery_issue,financial,complaint',
            'message'  => 'required|string',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $ticket = $user->supportTickets()->create([
            'title'    => $data['title'],
            'category' => $data['category'],
            'order_id' => $data['order_id'] ?? null,
            'status'   => 'open',
            'priority' => 'medium',
        ]);

        $ticket->messages()->create([
            'sender_id'   => $user->id,
            'sender_name' => $user->name,
            'message'     => $data['message'],
            'is_read'     => false,
        ]);

        $ticket->load('messages');

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data'    => new SupportTicketResource($ticket),
        ], 201);
    }

    public function sendMessage(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $ticket = $user->supportTickets()->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Support ticket not found',
                'code'    => 'TICKET_NOT_FOUND',
            ], 404);
        }

        if ($ticket->status === 'resolved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send a message to a resolved ticket',
                'code'    => 'TICKET_RESOLVED',
            ], 422);
        }

        $data = $request->validate([
            'message' => 'required|string',
        ]);

        $message = $ticket->messages()->create([
            'sender_id'   => $user->id,
            'sender_name' => $user->name,
            'message'     => $data['message'],
            'is_read'     => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => new SupportMessageResource($message),
        ], 201);
    }
}
