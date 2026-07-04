<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutValue;
use Illuminate\Http\Request;

class AboutValueController extends Controller
{
    public function index()
    {
        $values = AboutValue::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.about-values.index', compact('values'));
    }

    public function create()
    {
        return view('admin.cms.about-values.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text.en'    => 'required|string|max:255',
            'text.ar'    => 'required|string|max:255',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'required|integer|min:0',
        ]);

        AboutValue::create([
            'text'       => $validated['text'],
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.about-values.index')
            ->with('success', 'Value created successfully.');
    }

    public function edit(AboutValue $aboutValue)
    {
        return view('admin.cms.about-values.edit', ['value' => $aboutValue]);
    }

    public function update(Request $request, AboutValue $aboutValue)
    {
        $validated = $request->validate([
            'text.en'    => 'required|string|max:255',
            'text.ar'    => 'required|string|max:255',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'required|integer|min:0',
        ]);

        $aboutValue->update([
            'text'       => $validated['text'],
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.about-values.index')
            ->with('success', 'Value updated successfully.');
    }

    public function destroy(AboutValue $aboutValue)
    {
        $aboutValue->delete();

        return redirect()->route('admin.cms.about-values.index')
            ->with('success', 'Value deleted successfully.');
    }
}
