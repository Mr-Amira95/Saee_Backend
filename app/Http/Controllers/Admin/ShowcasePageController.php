<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShowcasePage;
use Illuminate\Http\Request;

class ShowcasePageController extends Controller
{
    public function index()
    {
        $showcase = ShowcasePage::instance();

        return view('admin.cms.showcase-page.index', compact('showcase'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'page_badge.en'       => 'nullable|string|max:255',
            'page_badge.ar'       => 'nullable|string|max:255',
            'page_title.en'       => 'required|string|max:255',
            'page_title.ar'       => 'required|string|max:255',
            'page_subtitle.en'    => 'nullable|string|max:1000',
            'page_subtitle.ar'    => 'nullable|string|max:1000',
            'section_badge.en'    => 'nullable|string|max:255',
            'section_badge.ar'    => 'nullable|string|max:255',
            'section_title.en'    => 'required|string|max:255',
            'section_title.ar'    => 'required|string|max:255',
            'section_subtitle.en' => 'nullable|string|max:1000',
            'section_subtitle.ar' => 'nullable|string|max:1000',
        ]);

        $showcase = ShowcasePage::instance();

        $showcase->update([
            'page_badge'        => $validated['page_badge'] ?? null,
            'page_title'        => $validated['page_title'],
            'page_subtitle'     => $validated['page_subtitle'] ?? null,
            'section_badge'     => $validated['section_badge'] ?? null,
            'section_title'     => $validated['section_title'],
            'section_subtitle'  => $validated['section_subtitle'] ?? null,
        ]);

        return redirect()->route('admin.cms.showcase-page.index')
            ->with('success', 'Showcase section updated successfully.');
    }
}
