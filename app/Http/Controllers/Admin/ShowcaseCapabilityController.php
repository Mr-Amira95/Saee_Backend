<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\ShowcaseCapability;
use Illuminate\Http\Request;

class ShowcaseCapabilityController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $capabilities = ShowcaseCapability::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.showcase-capabilities.index', compact('capabilities'));
    }

    public function create()
    {
        return view('admin.cms.showcase-capabilities.create');
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
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'showcase-capabilities');
        }

        ShowcaseCapability::create([
            'icon_path'  => $iconPath,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.showcase-capabilities.index')
            ->with('success', 'Capability created successfully.');
    }

    public function edit(ShowcaseCapability $showcaseCapability)
    {
        return view('admin.cms.showcase-capabilities.edit', ['capability' => $showcaseCapability]);
    }

    public function update(Request $request, ShowcaseCapability $showcaseCapability)
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

        $iconPath = $showcaseCapability->icon_path;
        if ($request->hasFile('icon_file')) {
            $this->deleteUploadedImage($showcaseCapability->icon_path);
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'showcase-capabilities');
        }

        $showcaseCapability->update([
            'icon_path'  => $iconPath,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.showcase-capabilities.index')
            ->with('success', 'Capability updated successfully.');
    }

    public function destroy(ShowcaseCapability $showcaseCapability)
    {
        $this->deleteUploadedImage($showcaseCapability->icon_path);
        $showcaseCapability->delete();

        return redirect()->route('admin.cms.showcase-capabilities.index')
            ->with('success', 'Capability deleted successfully.');
    }
}
