<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Public\Concerns\FormatsMedia;
use App\Models\Service;
use App\Models\ServicesPage;
use Illuminate\Http\JsonResponse;

class ServicesController extends Controller
{
    use FormatsMedia;

    public function show(): JsonResponse
    {
        $page = ServicesPage::instance();
        $items = Service::where('status', 'active')->orderBy('sort_order')->get();

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
            'items' => $items->map(fn (Service $service) => [
                'icon' => $this->mediaUrl($service->icon_path),
                'title' => $service->title,
                'subtitle' => $service->subtitle,
            ]),
        ]);
    }
}
