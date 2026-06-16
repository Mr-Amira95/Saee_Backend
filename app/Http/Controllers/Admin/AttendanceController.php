<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * View all attendance logs.
     */
    public function index(Request $request)
    {
        $query = Attendance::with('user');

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        } else {
            // Default to today if no filter, or keep empty if they want to browse all history?
            // Let's not filter by default but order by latest logs
        }

        // Filter by Role
        if ($request->filled('role')) {
            $role = $request->input('role');
            $query->whereHas('user', function ($q) use ($role) {
                if ($role === 'admin') {
                    $q->whereIn('role', ['admin', 'superadmin']);
                } else {
                    $q->where('role', $role);
                }
            });
        }

        // Filter by Search (User name)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('check_in_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.settings.attendance.index', compact('attendances'));
    }

    /**
     * Action to check-in.
     */
    public function checkIn(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        // Check if already checked in today
        $existing = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today.'
            ], 422);
        }

        $location = $request->input('location'); // 'lat,long' or null

        $attendance = Attendance::create([
            'user_id'            => $userId,
            'date'               => $today,
            'check_in_at'        => now(),
            'check_in_location'  => $location,
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'Checked in successfully at ' . now()->format('H:i') . '.',
            'check_in'  => now()->format('H:i'),
        ]);
    }

    /**
     * Action to check-out.
     */
    public function checkOut(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        // Find check-in record
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'You have not checked in today.'
            ], 422);
        }

        if ($attendance->check_out_at) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out today.'
            ], 422);
        }

        $location = $request->input('location');

        $attendance->update([
            'check_out_at'        => now(),
            'check_out_location'  => $location,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Checked out successfully at ' . now()->format('H:i') . '.',
            'check_out'  => now()->format('H:i'),
        ]);
    }

    /**
     * Action for admin to manually update/correct log times.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'check_in_at'  => 'required|date_format:Y-m-d H:i:s',
            'check_out_at' => 'nullable|date_format:Y-m-d H:i:s|after:check_in_at',
        ]);

        $attendance->update([
            'check_in_at'  => $validated['check_in_at'],
            'check_out_at' => $validated['check_out_at'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Attendance record updated.');
    }
}
