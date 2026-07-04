<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\BusinessBenefit;
use App\Models\ForBusinessPage;
use Illuminate\Http\JsonResponse;

class ForBusinessController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $page = ForBusinessPage::instance();
        $benefits = BusinessBenefit::where('status', 'active')->orderBy('sort_order')->get();

        return response()->json([
            'badge' => $page->page_badge,
            'title' => $page->page_title,
            'subtitle' => $page->page_subtitle,
            'benefits' => $benefits->map(fn (BusinessBenefit $benefit) => [
                'icon' => $this->mediaUrl($benefit->icon_path),
                'title' => $benefit->title,
                'subtitle' => $benefit->subtitle,
            ]),
        ]);
    }
}
