<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\BusinessBenefit;
use Illuminate\Http\Request;

class BusinessBenefitController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $benefits = BusinessBenefit::orderBy('sort_order')->orderBy('created_at', 'desc')->get();

        return view('admin.cms.business-benefits.index', compact('benefits'));
    }

    public function create()
    {
        return view('admin.cms.business-benefits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'icon_file'   => 'nullable|file|mimes:svg|max:512',
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $iconPath = null;
        if ($request->hasFile('icon_file')) {
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'business-benefits');
        }

        BusinessBenefit::create([
            'icon_path'  => $iconPath,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.business-benefits.index')
            ->with('success', 'Benefit created successfully.');
    }

    public function edit(BusinessBenefit $businessBenefit)
    {
        return view('admin.cms.business-benefits.edit', ['benefit' => $businessBenefit]);
    }

    public function update(Request $request, BusinessBenefit $businessBenefit)
    {
        $validated = $request->validate([
            'icon_file'   => 'nullable|file|mimes:svg|max:512',
            'title.en'    => 'required|string|max:255',
            'title.ar'    => 'required|string|max:255',
            'subtitle.en' => 'nullable|string|max:1000',
            'subtitle.ar' => 'nullable|string|max:1000',
            'status'      => 'required|in:active,inactive',
            'sort_order'  => 'required|integer|min:0',
        ]);

        $iconPath = $businessBenefit->icon_path;
        if ($request->hasFile('icon_file')) {
            $this->deleteUploadedImage($businessBenefit->icon_path);
            $iconPath = $this->storeUploadedImage($request->file('icon_file'), 'business-benefits');
        }

        $businessBenefit->update([
            'icon_path'  => $iconPath,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.business-benefits.index')
            ->with('success', 'Benefit updated successfully.');
    }

    public function destroy(BusinessBenefit $businessBenefit)
    {
        $this->deleteUploadedImage($businessBenefit->icon_path);
        $businessBenefit->delete();

        return redirect()->route('admin.cms.business-benefits.index')
            ->with('success', 'Benefit deleted successfully.');
    }
}
