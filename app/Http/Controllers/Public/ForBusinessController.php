<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ForBusinessPage;
use Illuminate\Http\JsonResponse;

class ForBusinessController extends Controller
{
    public function show(): JsonResponse
    {
        $page = ForBusinessPage::instance();

        return response()->json([
            'badge' => $page->page_badge,
            'title' => $page->page_title,
            'subtitle' => $page->page_subtitle,
        ]);
    }
}
