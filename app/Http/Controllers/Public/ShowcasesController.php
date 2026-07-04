<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\ShowcaseCapability;
use App\Models\ShowcaseHowItWork;
use App\Models\ShowcaseMetric;
use App\Models\ShowcasePage;
use App\Models\ShowcaseScreenshot;
use Illuminate\Http\JsonResponse;

class ShowcasesController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $page = ShowcasePage::instance();
        $capabilities = ShowcaseCapability::where('status', 'active')->orderBy('sort_order')->get();
        $howItWorks = ShowcaseHowItWork::where('status', 'active')->orderBy('sort_order')->get();
        $metrics = ShowcaseMetric::orderBy('sort_order')->get();
        $screenshots = ShowcaseScreenshot::where('status', 'active')->orderBy('sort_order')->get();

        return response()->json([
            'page' => [
                'badge' => $page->page_badge,
                'title' => $page->page_title,
                'subtitle' => $page->page_subtitle,
            ],
            'section' => [
                'badge' => $page->section_badge,
                'title' => $page->section_title,
                'subtitle' => $page->section_subtitle,
            ],
            'capabilities' => $capabilities->map(fn (ShowcaseCapability $item) => [
                'icon' => $this->mediaUrl($item->icon_path),
                'title' => $item->title,
                'subtitle' => $item->subtitle,
            ]),
            'howItWorks' => $howItWorks->map(fn (ShowcaseHowItWork $item) => [
                'icon' => $this->mediaUrl($item->icon_path),
                'title' => $item->title,
                'subtitle' => $item->subtitle,
                'order' => $item->sort_order,
            ]),
            'metrics' => $metrics->map(fn (ShowcaseMetric $metric) => [
                'key' => $metric->key,
                'value' => $metric->value,
            ]),
            'screenshots' => $screenshots->map(fn (ShowcaseScreenshot $item) => [
                'category' => $item->category,
                'image' => $this->mediaUrl($item->image_path),
                'title' => $item->title,
                'subtitle' => $item->subtitle,
            ]),
        ]);
    }
}
