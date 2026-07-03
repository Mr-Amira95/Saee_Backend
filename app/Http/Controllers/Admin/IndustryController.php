<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\Request;

class IndustryController extends Controller
{
    public function index()
    {
        $industries = Industry::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.industries.index', compact('industries'));
    }

    public function create()
    {
        return view('admin.cms.industries.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'icon'        => 'nullable|string|max:100',
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        Industry::create([
            'icon'       => $validated['icon'] ?? null,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.industries.index')
            ->with('success', 'Industry created successfully.');
    }

    public function edit(Industry $industry)
    {
        return view('admin.cms.industries.edit', compact('industry'));
    }

    public function update(Request $request, Industry $industry)
    {
        $validated = $request->validate([
            'icon'        => 'nullable|string|max:100',
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $industry->update([
            'icon'       => $validated['icon'] ?? null,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.industries.index')
            ->with('success', 'Industry updated successfully.');
    }

    public function destroy(Industry $industry)
    {
        $industry->delete();

        return redirect()->route('admin.cms.industries.index')
            ->with('success', 'Industry deleted successfully.');
    }
}
