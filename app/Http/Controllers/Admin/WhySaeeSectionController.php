<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhySaeeSection;
use Illuminate\Http\Request;

class WhySaeeSectionController extends Controller
{
    public function index()
    {
        $whySaee = WhySaeeSection::instance();

        return view('admin.cms.why-saee-page.index', compact('whySaee'));
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

        $whySaee = WhySaeeSection::instance();

        $whySaee->update([
            'badge'    => $validated['badge'] ?? null,
            'title'    => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
        ]);

        return redirect()->route('admin.cms.why-saee-page.index')
            ->with('success', 'Why Sa\'ee section updated successfully.');
    }
}
