<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $services = Service::orderBy('sort_order')->orderBy('created_at', 'desc')->get();
        return view('admin.cms.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.cms.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title.en'     => 'required|string|max:255',
            'title.ar'     => 'required|string|max:255',
            'subtitle.en'  => 'nullable|string',
            'subtitle.ar'  => 'nullable|string',
            'icon_file'    => 'nullable|file|mimes:svg|max:512',
            'status'       => 'required|in:active,inactive',
            'sort_order'   => 'required|integer|min:0',
        ]);

        $iconPath = null;
        if ($request->hasFile('icon_file')) {
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'services');
        }

        Service::create([
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'icon_path'  => $iconPath,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        return view('admin.cms.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'title.en'     => 'required|string|max:255',
            'title.ar'     => 'required|string|max:255',
            'subtitle.en'  => 'nullable|string',
            'subtitle.ar'  => 'nullable|string',
            'icon_file'    => 'nullable|file|mimes:svg|max:512',
            'status'       => 'required|in:active,inactive',
            'sort_order'   => 'required|integer|min:0',
        ]);

        $iconPath = $service->icon_path;
        if ($request->hasFile('icon_file')) {
            $this->deleteUploadedImage($service->icon_path);
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'services');
        }

        $service->update([
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'icon_path'  => $iconPath,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $this->deleteUploadedImage($service->icon_path);
        $service->delete();

        return redirect()->route('admin.cms.services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
