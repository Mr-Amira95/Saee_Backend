<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\ShowcaseHowItWork;
use Illuminate\Http\Request;

class ShowcaseHowItWorkController extends Controller
{
    use HandlesImageUploads;

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
            'icon_file'     => 'nullable|file|mimes:svg|max:512',
            'title.en'      => 'required|string|max:255',
            'title.ar'      => 'required|string|max:255',
            'subtitle.en'   => 'nullable|string|max:1000',
            'subtitle.ar'   => 'nullable|string|max:1000',
            'status'        => 'required|in:active,inactive',
            'sort_order'    => 'required|integer|min:0',
        ]);

        $iconPath = null;
        if ($request->hasFile('icon_file')) {
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'showcase-how-it-works');
        }

        ShowcaseHowItWork::create([
            'icon_path'  => $iconPath,
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
            'icon_file'     => 'nullable|file|mimes:svg|max:512',
            'title.en'      => 'required|string|max:255',
            'title.ar'      => 'required|string|max:255',
            'subtitle.en'   => 'nullable|string|max:1000',
            'subtitle.ar'   => 'nullable|string|max:1000',
            'status'        => 'required|in:active,inactive',
            'sort_order'    => 'required|integer|min:0',
        ]);

        $iconPath = $showcaseHowItWork->icon_path;
        if ($request->hasFile('icon_file')) {
            $this->deleteUploadedImage($showcaseHowItWork->icon_path);
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'showcase-how-it-works');
        }

        $showcaseHowItWork->update([
            'icon_path'  => $iconPath,
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
        $this->deleteUploadedImage($showcaseHowItWork->icon_path);
        $showcaseHowItWork->delete();

        return redirect()->route('admin.cms.showcase-how-it-works.index')
            ->with('success', 'Step deleted successfully.');
    }
}
