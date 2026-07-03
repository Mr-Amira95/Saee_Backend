<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\AboutPage;
use Illuminate\Http\JsonResponse;

class AboutController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $page = AboutPage::instance();

        return response()->json([
            'badge' => $page->page_badge,
            'title' => $page->page_title,
            'subtitle' => $page->page_subtitle,
            'image' => $this->mediaUrl($page->image_path),
            'mission' => $page->mission,
            'vision' => $page->vision,
        ]);
    }
}
