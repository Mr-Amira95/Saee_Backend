<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhySaeeReason;
use Illuminate\Http\Request;

class WhySaeeReasonController extends Controller
{
    public function index()
    {
        $reasons = WhySaeeReason::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.why-saee-reasons.index', compact('reasons'));
    }

    public function create()
    {
        return view('admin.cms.why-saee-reasons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string',
            'subtitle.ar' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        WhySaeeReason::create($validated);

        return redirect()->route('admin.cms.why-saee-reasons.index')
            ->with('success', 'Reason created successfully.');
    }

    public function edit(WhySaeeReason $whySaeeReason)
    {
        return view('admin.cms.why-saee-reasons.edit', ['reason' => $whySaeeReason]);
    }

    public function update(Request $request, WhySaeeReason $whySaeeReason)
    {
        $validated = $request->validate([
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string',
            'subtitle.ar' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $whySaeeReason->update($validated);

        return redirect()->route('admin.cms.why-saee-reasons.index')
            ->with('success', 'Reason updated successfully.');
    }

    public function destroy(WhySaeeReason $whySaeeReason)
    {
        $whySaeeReason->delete();

        return redirect()->route('admin.cms.why-saee-reasons.index')
            ->with('success', 'Reason deleted successfully.');
    }
}
