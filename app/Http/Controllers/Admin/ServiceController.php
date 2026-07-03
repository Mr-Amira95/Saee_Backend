<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
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
            'icon'         => 'nullable|string|max:100',
            'status'       => 'required|in:active,inactive',
            'sort_order'   => 'required|integer|min:0',
        ]);

        Service::create($validated);

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
            'icon'         => 'nullable|string|max:100',
            'status'       => 'required|in:active,inactive',
            'sort_order'   => 'required|integer|min:0',
        ]);

        $service->update($validated);

        return redirect()->route('admin.cms.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('admin.cms.services.index')
            ->with('success', 'Service deleted successfully.');
    }
}
