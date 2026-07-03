<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use Illuminate\Http\Request;

class AboutPageController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $page = AboutPage::instance();

        return view('admin.cms.about-page.index', compact('page'));
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
            'mission.en'       => 'required|string',
            'mission.ar'       => 'required|string',
            'vision.en'        => 'required|string',
            'vision.ar'        => 'required|string',
            'image_file'       => 'nullable|image|max:4096',
            'image_path'       => 'nullable|string|max:2048',
        ]);

        $page = AboutPage::instance();

        $imagePath = $validated['image_path'] ?? $page->image_path;

        if ($request->hasFile('image_file')) {
            $this->deleteUploadedImage($page->image_path);
            $imagePath = $this->storeUploadedImage($request->file('image_file'), 'about');
        }

        $page->update([
            'page_badge'    => $validated['page_badge'] ?? null,
            'page_title'    => $validated['page_title'],
            'page_subtitle' => $validated['page_subtitle'] ?? null,
            'image_path'    => $imagePath,
            'mission'       => $validated['mission'],
            'vision'        => $validated['vision'],
        ]);

        return redirect()->route('admin.cms.about-page.index')
            ->with('success', 'About page updated successfully.');
    }
}
