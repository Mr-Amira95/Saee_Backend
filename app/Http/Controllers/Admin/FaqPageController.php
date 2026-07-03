<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqPage;
use Illuminate\Http\Request;

class FaqPageController extends Controller
{
    public function index()
    {
        $faqPage = FaqPage::instance();

        return view('admin.cms.faq-page.index', compact('faqPage'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'page_badge.en'    => 'nullable|string|max:255',
            'page_badge.ar'    => 'nullable|string|max:255',
            'page_title.en'    => 'required|string|max:255',
            'page_title.ar'    => 'required|string|max:255',
            'page_subtitle.en' => 'nullable|string|max:1000',
            'page_subtitle.ar' => 'nullable|string|max:1000',
        ]);

        $faqPage = FaqPage::instance();

        $faqPage->update([
            'page_badge'    => $validated['page_badge'] ?? null,
            'page_title'    => $validated['page_title'],
            'page_subtitle' => $validated['page_subtitle'] ?? null,
        ]);

        return redirect()->route('admin.cms.faq-page.index')
            ->with('success', 'FAQ page updated successfully.');
    }
}
