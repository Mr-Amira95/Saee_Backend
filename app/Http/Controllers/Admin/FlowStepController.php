<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\FlowStep;
use Illuminate\Http\Request;

class FlowStepController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $steps = FlowStep::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.flow-steps.index', compact('steps'));
    }

    public function create()
    {
        return view('admin.cms.flow-steps.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
            'image_file'  => 'nullable|image|max:4096',
            'image_path'  => 'nullable|string|max:2048',
            'sort_order'  => 'required|integer|min:0',
            'status'      => 'required|in:active,inactive',
        ]);

        $imagePath = $validated['image_path'] ?? null;

        if ($request->hasFile('image_file')) {
            $imagePath = $this->storeUploadedImage($request->file('image_file'), 'flow');
        }

        FlowStep::create([
            'image_path' => $imagePath,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'sort_order' => $validated['sort_order'],
            'status'     => $validated['status'],
        ]);

        return redirect()->route('admin.cms.flow-steps.index')
            ->with('success', 'Flow step created successfully.');
    }

    public function edit(FlowStep $flowStep)
    {
        return view('admin.cms.flow-steps.edit', ['step' => $flowStep]);
    }

    public function update(Request $request, FlowStep $flowStep)
    {
        $validated = $request->validate([
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
            'image_file'  => 'nullable|image|max:4096',
            'image_path'  => 'nullable|string|max:2048',
            'sort_order'  => 'required|integer|min:0',
            'status'      => 'required|in:active,inactive',
        ]);

        $imagePath = $validated['image_path'] ?? $flowStep->image_path;

        if ($request->hasFile('image_file')) {
            $this->deleteUploadedImage($flowStep->image_path);
            $imagePath = $this->storeUploadedImage($request->file('image_file'), 'flow');
        }

        $flowStep->update([
            'image_path' => $imagePath,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'sort_order' => $validated['sort_order'],
            'status'     => $validated['status'],
        ]);

        return redirect()->route('admin.cms.flow-steps.index')
            ->with('success', 'Flow step updated successfully.');
    }

    public function destroy(FlowStep $flowStep)
    {
        $this->deleteUploadedImage($flowStep->image_path);

        $flowStep->delete();

        return redirect()->route('admin.cms.flow-steps.index')
            ->with('success', 'Flow step deleted successfully.');
    }
}
