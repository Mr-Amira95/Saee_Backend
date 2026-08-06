<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\DownloadAppPage;
use Illuminate\Http\JsonResponse;

class DownloadAppController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $page = DownloadAppPage::instance();

        return response()->json([
            'badge' => $page->badge,
            'title' => $page->title,
            'subtitle' => $page->subtitle,
            'image' => $this->mediaUrl($page->image_path),
            'androidUrl' => $page->android_url,
            'iosUrl' => $page->ios_url,
        ]);
    }
}
