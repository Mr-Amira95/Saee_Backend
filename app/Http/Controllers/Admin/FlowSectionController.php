<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlowSection;
use Illuminate\Http\Request;

class FlowSectionController extends Controller
{
    public function index()
    {
        $flow = FlowSection::instance();

        return view('admin.cms.flow.index', compact('flow'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'badge.en'    => 'nullable|string|max:255',
            'badge.ar'    => 'nullable|string|max:255',
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
        ]);

        $flow = FlowSection::instance();

        $flow->update([
            'badge'    => $validated['badge'] ?? null,
            'title'    => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
        ]);

        return redirect()->route('admin.cms.flow.index')
            ->with('success', 'Flow section updated successfully.');
    }
}
