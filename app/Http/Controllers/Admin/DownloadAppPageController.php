<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\DownloadAppPage;
use Illuminate\Http\Request;

class DownloadAppPageController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $downloadApp = DownloadAppPage::instance();

        return view('admin.cms.download-app.index', compact('downloadApp'));
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
            'android_url'   => 'nullable|string|max:2048',
            'ios_url'       => 'nullable|string|max:2048',
        ]);

        $downloadApp = DownloadAppPage::instance();

        $imagePath = $validated['image_path'] ?? $downloadApp->image_path;

        if ($request->hasFile('image_file')) {
            $this->deleteUploadedImage($downloadApp->image_path);
            $imagePath = $this->storeUploadedImage($request->file('image_file'), 'download-app');
        }

        $downloadApp->update([
            'badge'       => $validated['badge'] ?? null,
            'title'       => $validated['title'],
            'subtitle'    => $validated['subtitle'] ?? null,
            'image_path'  => $imagePath,
            'android_url' => $validated['android_url'] ?? null,
            'ios_url'     => $validated['ios_url'] ?? null,
        ]);

        return redirect()->route('admin.cms.download-app-page.index')
            ->with('success', __('Download Application page updated successfully.'));
    }
}
