<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\ShowcaseScreenshot;
use Illuminate\Http\Request;

class ShowcaseScreenshotController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $applicationScreenshots = ShowcaseScreenshot::where('category', 'application')
            ->orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        $portalScreenshots = ShowcaseScreenshot::where('category', 'portal')
            ->orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.showcase-screenshots.index', compact('applicationScreenshots', 'portalScreenshots'));
    }

    public function create(Request $request)
    {
        $category = $request->query('category') === 'portal' ? 'portal' : 'application';

        return view('admin.cms.showcase-screenshots.create', compact('category'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'    => 'required|in:application,portal',
            'image_file'  => 'required|image|max:4096',
            'title.en'    => 'nullable|string|max:255',
            'title.ar'    => 'nullable|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $imagePath = $this->storeUploadedImage($request->file('image_file'), 'showcase-screenshots');

        ShowcaseScreenshot::create([
            'category'   => $validated['category'],
            'image_path' => $imagePath,
            'title'      => $validated['title'] ?? null,
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.showcase-screenshots.index')
            ->with('success', 'Screenshot uploaded successfully.');
    }

    public function edit(ShowcaseScreenshot $showcaseScreenshot)
    {
        return view('admin.cms.showcase-screenshots.edit', ['screenshot' => $showcaseScreenshot]);
    }

    public function update(Request $request, ShowcaseScreenshot $showcaseScreenshot)
    {
        $validated = $request->validate([
            'category'    => 'required|in:application,portal',
            'image_file'  => 'nullable|image|max:4096',
            'title.en'    => 'nullable|string|max:255',
            'title.ar'    => 'nullable|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $imagePath = $showcaseScreenshot->image_path;
        if ($request->hasFile('image_file')) {
            $this->deleteUploadedImage($showcaseScreenshot->image_path);
            $imagePath = $this->storeUploadedImage($request->file('image_file'), 'showcase-screenshots');
        }

        $showcaseScreenshot->update([
            'category'   => $validated['category'],
            'image_path' => $imagePath,
            'title'      => $validated['title'] ?? null,
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.showcase-screenshots.index')
            ->with('success', 'Screenshot updated successfully.');
    }

    public function destroy(ShowcaseScreenshot $showcaseScreenshot)
    {
        $this->deleteUploadedImage($showcaseScreenshot->image_path);
        $showcaseScreenshot->delete();

        return redirect()->route('admin.cms.showcase-screenshots.index')
            ->with('success', 'Screenshot deleted successfully.');
    }
}
