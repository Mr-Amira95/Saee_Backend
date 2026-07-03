<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqPage;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function show(): JsonResponse
    {
        $page = FaqPage::instance();
        $items = Faq::where('status', 'active')->orderBy('sort_order')->get();

        return response()->json([
            'page' => [
                'badge' => $page->page_badge,
                'title' => $page->page_title,
                'subtitle' => $page->page_subtitle,
            ],
            'items' => $items->map(fn (Faq $faq) => [
                'question' => $faq->question,
                'answer' => $faq->answer,
            ]),
        ]);
    }
}
