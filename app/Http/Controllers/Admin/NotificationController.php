<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * View notifications dashboard.
     */
    public function index(Request $request)
    {
        $notifications = SystemNotification::with('user', 'creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $users = User::where('status', 'active')->orderBy('name')->get();

        return view('admin.notifications.index', compact('notifications', 'users'));
    }

    /**
     * Dispatch a notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:150',
            'message' => 'required|string',
            'target'  => 'required|string', // 'all', 'driver', 'client', 'specific'
            'user_id' => 'required_if:target,specific|nullable|exists:users,id',
            'type'    => 'required|in:info,success,warning,danger',
            'link'    => 'nullable|string|max:255',
        ]);

        $target = $request->input('target');
        $role = null;
        $userId = null;

        if ($target === 'driver') {
            $role = 'driver';
        } elseif ($target === 'client') {
            $role = 'client_master';
        } elseif ($target === 'specific') {
            $userId = $request->input('user_id');
        } else {
            $role = 'all';
        }

        SystemNotification::create([
            'user_id'    => $userId,
            'role'       => $role,
            'title'      => $request->input('title'),
            'message'    => $request->input('message'),
            'link'       => $request->input('link'),
            'type'       => $request->input('type'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification dispatched successfully.');
    }

    /**
     * Fetch unread alerts for navigation bell (AJAX JSON).
     */
    public function getLatestUnread()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'notifications' => [], 'count' => 0]);
        }

        $notifications = SystemNotification::whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('role', $user->role)
                  ->orWhere('role', 'all');
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $count = SystemNotification::whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('role', $user->role)
                  ->orWhere('role', 'all');
            })
            ->count();

        return response()->json([
            'success'       => true,
            'notifications' => $notifications,
            'count'         => $count
        ]);
    }

    /**
     * Clear all unread notifications for logged-in user (AJAX).
     */
    public function markAllRead()
    {
        $user = Auth::user();
        
        SystemNotification::whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('role', $user->role)
                  ->orWhere('role', 'all');
            })
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
