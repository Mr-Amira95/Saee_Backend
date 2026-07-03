<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShowcaseHowItWork;
use Illuminate\Http\Request;

class ShowcaseHowItWorkController extends Controller
{
    public function index()
    {
        $steps = ShowcaseHowItWork::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.showcase-how-it-works.index', compact('steps'));
    }

    public function create()
    {
        return view('admin.cms.showcase-how-it-works.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'icon'          => 'nullable|string|max:100',
            'title.en'      => 'required|string|max:255',
            'title.ar'      => 'required|string|max:255',
            'subtitle.en'   => 'nullable|string|max:1000',
            'subtitle.ar'   => 'nullable|string|max:1000',
            'status'        => 'required|in:active,inactive',
            'sort_order'    => 'required|integer|min:0',
        ]);

        ShowcaseHowItWork::create([
            'icon'       => $validated['icon'] ?? null,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.showcase-how-it-works.index')
            ->with('success', 'Step created successfully.');
    }

    public function edit(ShowcaseHowItWork $showcaseHowItWork)
    {
        return view('admin.cms.showcase-how-it-works.edit', ['step' => $showcaseHowItWork]);
    }

    public function update(Request $request, ShowcaseHowItWork $showcaseHowItWork)
    {
        $validated = $request->validate([
            'icon'          => 'nullable|string|max:100',
            'title.en'      => 'required|string|max:255',
            'title.ar'      => 'required|string|max:255',
            'subtitle.en'   => 'nullable|string|max:1000',
            'subtitle.ar'   => 'nullable|string|max:1000',
            'status'        => 'required|in:active,inactive',
            'sort_order'    => 'required|integer|min:0',
        ]);

        $showcaseHowItWork->update([
            'icon'       => $validated['icon'] ?? null,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.showcase-how-it-works.index')
            ->with('success', 'Step updated successfully.');
    }

    public function destroy(ShowcaseHowItWork $showcaseHowItWork)
    {
        $showcaseHowItWork->delete();

        return redirect()->route('admin.cms.showcase-how-it-works.index')
            ->with('success', 'Step deleted successfully.');
    }
}
