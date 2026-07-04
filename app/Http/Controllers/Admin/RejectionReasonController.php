<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RejectionReason;
use Illuminate\Http\Request;

class RejectionReasonController extends Controller
{
    public function index(Request $request)
    {
        $reasons = RejectionReason::when($request->search, fn($q, $s) =>
                $q->where('reason', 'like', "%$s%")
                  ->orWhere('reason_ar', 'like', "%$s%")
            )
            ->orderBy('reason')
            ->paginate(20)
            ->withQueryString();

        return view('admin.settings.rejection_reasons.index', compact('reasons'));
    }

    public function create()
    {
        return view('admin.settings.rejection_reasons.create');
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasAdminAction('rejection_reasons.add'), 403);

        $data = $request->validate([
            'reason'    => 'required|string|max:255',
            'reason_ar' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        RejectionReason::create([
            'reason'    => $data['reason'],
            'reason_ar' => $data['reason_ar'] ?? null,
            'is_active' => isset($data['is_active']),
        ]);

        return redirect()->route('admin.rejection-reasons.index')
            ->with('success', 'Rejection reason added successfully.');
    }

    public function edit(RejectionReason $rejectionReason)
    {
        return view('admin.settings.rejection_reasons.edit', compact('rejectionReason'));
    }

    public function update(Request $request, RejectionReason $rejectionReason)
    {
        abort_unless($request->user()->hasAdminAction('rejection_reasons.edit'), 403);

        $data = $request->validate([
            'reason'    => 'required|string|max:255',
            'reason_ar' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $rejectionReason->update([
            'reason'    => $data['reason'],
            'reason_ar' => $data['reason_ar'] ?? null,
            'is_active' => isset($data['is_active']),
        ]);

        return redirect()->route('admin.rejection-reasons.index')
            ->with('success', 'Rejection reason updated successfully.');
    }

    public function toggle(RejectionReason $rejectionReason)
    {
        abort_unless(auth()->user()->hasAdminAction('rejection_reasons.activate'), 403);

        $rejectionReason->update(['is_active' => !$rejectionReason->is_active]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(RejectionReason $rejectionReason)
    {
        abort_unless(auth()->user()->hasAdminAction('rejection_reasons.delete'), 403);

        $rejectionReason->delete();

        return redirect()->route('admin.rejection-reasons.index')
            ->with('success', 'Rejection reason deleted.');
    }
}
