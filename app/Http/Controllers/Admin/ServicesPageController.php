<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicesPage;
use Illuminate\Http\Request;

class ServicesPageController extends Controller
{
    public function index()
    {
        $servicesPage = ServicesPage::instance();

        return view('admin.cms.services-page.index', compact('servicesPage'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'page_badge.en'       => 'nullable|string|max:255',
            'page_badge.ar'       => 'nullable|string|max:255',
            'page_title.en'       => 'nullable|string|max:255',
            'page_title.ar'       => 'nullable|string|max:255',
            'page_subtitle.en'    => 'nullable|string|max:1000',
            'page_subtitle.ar'    => 'nullable|string|max:1000',
            'section_badge.en'    => 'nullable|string|max:255',
            'section_badge.ar'    => 'nullable|string|max:255',
            'section_title.en'    => 'nullable|string|max:255',
            'section_title.ar'    => 'nullable|string|max:255',
            'section_subtitle.en' => 'nullable|string|max:1000',
            'section_subtitle.ar' => 'nullable|string|max:1000',
        ]);

        $servicesPage = ServicesPage::instance();

        $servicesPage->update([
            'page_badge'        => $validated['page_badge'] ?? null,
            'page_title'        => $validated['page_title'] ?? null,
            'page_subtitle'     => $validated['page_subtitle'] ?? null,
            'section_badge'     => $validated['section_badge'] ?? null,
            'section_title'     => $validated['section_title'] ?? null,
            'section_subtitle'  => $validated['section_subtitle'] ?? null,
        ]);

        return redirect()->route('admin.cms.services-page.index')
            ->with('success', 'Services page updated successfully.');
    }
}
