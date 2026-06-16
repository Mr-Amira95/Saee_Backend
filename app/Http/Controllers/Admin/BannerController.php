<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderBy('created_at', 'desc')->get();
        return view('admin.cms.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.cms.banners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'image_file' => 'nullable|image|max:4096',
            'image_path' => 'nullable|string|max:2048',
            'link_url'   => 'nullable|string|max:2048',
            'link_text'  => 'nullable|string|max:255',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'required|integer|min:0',
        ]);

        $imagePath = $validated['image_path'] ?? null;

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/banners');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }
            $file->move($destinationPath, $fileName);
            $imagePath = '/uploads/banners/' . $fileName;
        }

        Banner::create([
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'],
            'image_path' => $imagePath,
            'link_url'   => $validated['link_url'],
            'link_text'  => $validated['link_text'],
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.banners.index')
            ->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.cms.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'image_file' => 'nullable|image|max:4096',
            'image_path' => 'nullable|string|max:2048',
            'link_url'   => 'nullable|string|max:2048',
            'link_text'  => 'nullable|string|max:255',
            'status'     => 'required|in:active,inactive',
            'sort_order' => 'required|integer|min:0',
        ]);

        $imagePath = $validated['image_path'] ?? $banner->image_path;

        if ($request->hasFile('image_file')) {
            // Delete old file if exists and was locally uploaded
            if ($banner->image_path && str_starts_with($banner->image_path, '/uploads/')) {
                $oldFile = public_path($banner->image_path);
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
            }

            $file = $request->file('image_file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/banners');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }
            $file->move($destinationPath, $fileName);
            $imagePath = '/uploads/banners/' . $fileName;
        }

        $banner->update([
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'],
            'image_path' => $imagePath,
            'link_url'   => $validated['link_url'],
            'link_text'  => $validated['link_text'],
            'status'     => $validated['status'],
            'sort_order' => $validated['sort_order'],
        ]);

        return redirect()->route('admin.cms.banners.index')
            ->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image_path && str_starts_with($banner->image_path, '/uploads/')) {
            $filePath = public_path($banner->image_path);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $banner->delete();

        return redirect()->route('admin.cms.banners.index')
            ->with('success', 'Banner deleted successfully.');
    }
}
