<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndustrySection;
use Illuminate\Http\Request;

class IndustrySectionController extends Controller
{
    public function index()
    {
        $industrySection = IndustrySection::instance();

        return view('admin.cms.industries-page.index', compact('industrySection'));
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

        $industrySection = IndustrySection::instance();

        $industrySection->update([
            'badge'    => $validated['badge'] ?? null,
            'title'    => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
        ]);

        return redirect()->route('admin.cms.industries-page.index')
            ->with('success', 'Industries section updated successfully.');
    }
}
