<?php

namespace App\Http\Controllers\Client;

use App\Models\SystemNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    private function baseQuery()
    {
        $userId = Auth::id();

        return SystemNotification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id')->where(function ($q2) {
                  $q2->whereIn('role', ['all', 'clients'])->orWhereNull('role');
              });
        });
    }

    public function index(Request $request): View|JsonResponse
    {
        $notifications = $this->baseQuery()->latest()->paginate(20);
        $unreadCount   = $this->baseQuery()->whereNull('read_at')->count();

        if ($request->query('json') || $request->expectsJson()) {
            return response()->json([
                'notifications' => $notifications->items(),
                'unread_count'  => $unreadCount,
            ]);
        }

        $profile = $this->getClientProfile();

        return view('client.notifications.index', compact('notifications', 'unreadCount', 'profile'));
    }

    public function markRead(int $id): RedirectResponse|JsonResponse
    {
        $notification = $this->baseQuery()->findOrFail($id);
        $notification->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllRead(): RedirectResponse|JsonResponse
    {
        $this->baseQuery()->whereNull('read_at')->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount(): JsonResponse
    {
        $count = $this->baseQuery()->whereNull('read_at')->count();

        return response()->json(['count' => $count]);
    }
}
