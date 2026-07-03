<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroStat;
use Illuminate\Http\Request;

class HeroStatController extends Controller
{
    public function index()
    {
        $stats = HeroStat::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.hero-stats.index', compact('stats'));
    }

    public function create()
    {
        return view('admin.cms.hero-stats.create');
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

        HeroStat::create([
            'key'        => $validated['key'],
            'value'      => $validated['value'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.hero-stats.index')
            ->with('success', 'Stat created successfully.');
    }

    public function edit(HeroStat $heroStat)
    {
        return view('admin.cms.hero-stats.edit', ['stat' => $heroStat]);
    }

    public function update(Request $request, HeroStat $heroStat)
    {
        $validated = $request->validate([
            'key.en'     => 'required|string|max:255',
            'key.ar'     => 'required|string|max:255',
            'value.en'   => 'required|string|max:255',
            'value.ar'   => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
        ]);

        $heroStat->update([
            'key'        => $validated['key'],
            'value'      => $validated['value'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.hero-stats.index')
            ->with('success', 'Stat updated successfully.');
    }

    public function destroy(HeroStat $heroStat)
    {
        $heroStat->delete();

        return redirect()->route('admin.cms.hero-stats.index')
            ->with('success', 'Stat deleted successfully.');
    }
}
