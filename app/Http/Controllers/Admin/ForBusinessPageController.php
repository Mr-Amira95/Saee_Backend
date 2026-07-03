<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForBusinessPage;
use Illuminate\Http\Request;

class ForBusinessPageController extends Controller
{
    public function index()
    {
        $page = ForBusinessPage::instance();

        return view('admin.cms.for-business-page.index', compact('page'));
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

        $page = ForBusinessPage::instance();

        $page->update([
            'page_badge'    => $validated['page_badge'] ?? null,
            'page_title'    => $validated['page_title'],
            'page_subtitle' => $validated['page_subtitle'] ?? null,
        ]);

        return redirect()->route('admin.cms.for-business-page.index')
            ->with('success', 'For Businesses page updated successfully.');
    }
}
