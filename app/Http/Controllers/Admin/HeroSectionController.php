<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use Illuminate\Http\Request;

class HeroSectionController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $hero = HeroSection::instance();

        return view('admin.cms.hero.index', compact('hero'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'badge.en'      => 'nullable|string|max:255',
            'badge.ar'      => 'nullable|string|max:255',
            'title.en'      => 'required|string|max:255',
            'title.ar'      => 'required|string|max:255',
            'subtitle.en'   => 'nullable|string|max:1000',
            'subtitle.ar'   => 'nullable|string|max:1000',
            'image_file'    => 'nullable|image|max:4096',
            'image_path'    => 'nullable|string|max:2048',
        ]);

        $hero = HeroSection::instance();

        $imagePath = $validated['image_path'] ?? $hero->image_path;

        if ($request->hasFile('image_file')) {
            $this->deleteUploadedImage($hero->image_path);
            $imagePath = $this->storeUploadedImage($request->file('image_file'), 'hero');
        }

        $hero->update([
            'badge'      => $validated['badge'] ?? null,
            'title'      => $validated['title'],
            'subtitle'   => $validated['subtitle'] ?? null,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.cms.hero.index')
            ->with('success', 'Hero section updated successfully.');
    }
}
