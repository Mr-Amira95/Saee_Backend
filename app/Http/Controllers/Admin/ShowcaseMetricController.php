<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShowcaseMetric;
use Illuminate\Http\Request;

class ShowcaseMetricController extends Controller
{
    public function index()
    {
        $metrics = ShowcaseMetric::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.showcase-metrics.index', compact('metrics'));
    }

    public function create()
    {
        return view('admin.cms.showcase-metrics.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key.en'     => 'required|string|max:255',
            'key.ar'     => 'required|string|max:255',
            'value.en'   => 'required|string|max:255',
            'value.ar'   => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
        ]);

        ShowcaseMetric::create([
            'key'        => $validated['key'],
            'value'      => $validated['value'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.showcase-metrics.index')
            ->with('success', 'Metric created successfully.');
    }

    public function edit(ShowcaseMetric $showcaseMetric)
    {
        return view('admin.cms.showcase-metrics.edit', ['metric' => $showcaseMetric]);
    }

    public function update(Request $request, ShowcaseMetric $showcaseMetric)
    {
        $validated = $request->validate([
            'key.en'     => 'required|string|max:255',
            'key.ar'     => 'required|string|max:255',
            'value.en'   => 'required|string|max:255',
            'value.ar'   => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
        ]);

        $showcaseMetric->update([
            'key'        => $validated['key'],
            'value'      => $validated['value'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.showcase-metrics.index')
            ->with('success', 'Metric updated successfully.');
    }

    public function destroy(ShowcaseMetric $showcaseMetric)
    {
        $showcaseMetric->delete();

        return redirect()->route('admin.cms.showcase-metrics.index')
            ->with('success', 'Metric deleted successfully.');
    }
}
