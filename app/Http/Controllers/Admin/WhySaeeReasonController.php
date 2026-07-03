<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\WhySaeeReason;
use Illuminate\Http\Request;

class WhySaeeReasonController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $reasons = WhySaeeReason::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.why-saee-reasons.index', compact('reasons'));
    }

    public function create()
    {
        return view('admin.cms.why-saee-reasons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string',
            'subtitle.ar' => 'nullable|string',
            'icon_file'   => 'nullable|file|mimes:svg|max:512',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $iconPath = null;
        if ($request->hasFile('icon_file')) {
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'why-saee-reasons');
        }

        WhySaeeReason::create([
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'icon_path'  => $iconPath,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.why-saee-reasons.index')
            ->with('success', 'Reason created successfully.');
    }

    public function edit(WhySaeeReason $whySaeeReason)
    {
        return view('admin.cms.why-saee-reasons.edit', ['reason' => $whySaeeReason]);
    }

    public function update(Request $request, WhySaeeReason $whySaeeReason)
    {
        $validated = $request->validate([
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string',
            'subtitle.ar' => 'nullable|string',
            'icon_file'   => 'nullable|file|mimes:svg|max:512',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $iconPath = $whySaeeReason->icon_path;
        if ($request->hasFile('icon_file')) {
            $this->deleteUploadedImage($whySaeeReason->icon_path);
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'why-saee-reasons');
        }

        $whySaeeReason->update([
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'icon_path'  => $iconPath,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.why-saee-reasons.index')
            ->with('success', 'Reason updated successfully.');
    }

    public function destroy(WhySaeeReason $whySaeeReason)
    {
        $this->deleteUploadedImage($whySaeeReason->icon_path);
        $whySaeeReason->delete();

        return redirect()->route('admin.cms.why-saee-reasons.index')
            ->with('success', 'Reason deleted successfully.');
    }
}
